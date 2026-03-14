<?php
// ini_set('display_errors',1);
// error_reporting(E_ALL);
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
header('Content-Type: application/json; charset=utf-8');
// SharedManager::print($_GET);
// SharedManager::print($_POST);
$controller = new PMSController();
switch ($_GET["action"]) {
    case "allList":
        $controller->listGIDs(0);
        break;
    case "getEmployeeRegistrationData":    
        $controller->getEmployeeRegistrationData();
        break;
    case "getLeaveRegistrationData":
        $controller->getLeaveRegistrationData();
        break;
    case "getLeaveData":
        $controller->getLeaveData($_GET["id"]);
        break;
    
    case "getUserEmpData":
        $controller->getUserEmpData($_GET["id"]);
        break;
    case "getShiftAllocations":
        $controller->getShiftAllocations($_GET["id"]);
        break;
    case "retrieveAllActiveStaff":
        $controller->retrieveAllActiveStaff();
        break;
    case "retrieveExtendedEmployeeInformation":
        $controller->retrieveExtendedEmployeeInformation();
        break;
    case "fetchStaffHierarchyStructure":
        $controller->fetchStaffHierarchyStructure();
        break;
    case "generateEmployeeReport":
        $controller->generateEmployeeReport();
        break;
    // case "delete":
    //     $user_id = $_GET['userId'] ?? 0;
    //     if ($user_id < 1 || empty($user_id)) {
    //         http_response_code(500);
    //         exit();
    //     }
    //     $user_id_response = $controller->deleteUser($user_id);
    //     echo json_encode($user_id_response, JSON_PRETTY_PRINT);
    //     http_response_code(200);
    default:
        break;
}

switch ($_POST["action"]) {
    case "getGIDSuggestions":
        $controller->getGIDSuggestions();
        break;
    case "getGIDDetails":
        $controller->getGIDDetails();
        break;
    case "getDepartment":
        $controller->getDepartment();
        break;
    case "fetchEmployeeBasicData":
        $controller->fetchEmployeeBasicData();
        break;
    case "submitRegistration":    
        $controller->submitRegistration();
        break;
    case "updateShiftAllocation":    
        $controller->updateShiftAllocation();
        break;
    case "submitAttendanceForDate":    
        $controller->submitAttendanceForDate();
        break;
    case "submitOvertimeForDate":    
        $controller->submitOvertimeForDate();
        break;
    case "fetchAttendanceData":    
        $controller->fetchAttendanceData(); 
        break;
    case "fetchOvertimeData":    
        $controller->fetchOvertimeData(); 
        break;
    case "submitTransferRegistration": // NEW ACTION for transfer form
        $controller->submitTransferRegistration();
        break;
    case "leaveRegister":
        $controller->leaveRegister();
        break;
    case "editLeave":
        $controller->updatePMSleave();
        break;
    case "editUser":
        $controller->updatePMSuser();
        break;
    case "searchEmployeeRegistration":
        $controller->searchEmployeeRegistration();
        break;
    case "getEmployeeRegistrationDetails":
        $controller->getEmployeeRegistrationDetails();
        break;
    case "getAttendanceDataWithDepartments":
        $controller->getAttendanceDataWithDepartments();
        break;
    case "deleteUser":
        $controller->deleteUser();
        break;
    case "filterStaffByDepartment":
        $controller->filterStaffByDepartment();
        break;
    case "filterStaffByRole":
        $controller->filterStaffByRole();
        break;
    case "advancedStaffSearch":
        $controller->advancedStaffSearch();
        break;
    case "fetchDepartmentsList":
        $controller->fetchDepartmentsList();
        break;
    case "fetchRolesList":
        $controller->fetchRolesList();
        break;
    case "fetchManagersList":
        $controller->fetchManagersList();
        break;
    case "fetchSponsorsList":
        $controller->fetchSponsorsList();
        break;
    case "resolveManagerHierarchyNames":
        $controller->resolveManagerHierarchyNames($_POST);
        break;
    default:
        break;
}
exit;

class PMSController {
    private $currentUserGroupID;

    public function __construct() {
        if(in_array(19, SharedManager::getUser()["Modules"])) {
            // Authorized
            $modulesID = "19";
        } else {
            $this->returnHttpResponse(403, "Not Authorized");
        }
        $this->currentModulesID = $modulesID;
    }



    public function getLeaveData($pms_id = "", $return = "")
    {
        if (!empty($pms_id)) {
            $id = $pms_id;
        } else {
            $id = $_GET["id"];
        }
        // SharedManager::print($id);
        // exit();
        // if(!is_numeric($id))
        //     returnHttpResponse(400, "invalid id");

        //$query = "SELECT * FROM order_release WHERE id = :p1";
         $query = "SELECT * FROM tbl_leave_management WHERE id = :p1 ORDER BY id DESC";
        $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];
        
            echo json_encode($response);
        
        exit;
    }

    public function getUserEmpData($pmsUser_id = "", $return = "")
    {
        if (!empty($pmsUser_id)) {
            $id = $pmsUser_id;
        } else {
            $id = $_GET["id"];
        }

        // if(!is_numeric($id))
        //     returnHttpResponse(400, "invalid id");

        //$query = "SELECT * FROM order_release WHERE id = :p1";
         $query = "SELECT * FROM employee_registration WHERE id = :p1 ORDER BY id DESC";
        $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];

        // SharedManager::print($department);
        // exit();
        
            echo json_encode($response);
        
        exit;
    }

public function getShiftAllocations() {
    try {
        // Get filter parameters
        $fromDate = $_GET['from_date'] ?? '';
        $toDate = $_GET['to_date'] ?? '';
        $subDepartment = $_GET['sub_department'] ?? '';
        $groupType = $_GET['group_type'] ?? '';
        $shiftType = $_GET['shift_type'] ?? '';
        $includeEmployees = $_GET['include_employees'] ?? 'false';
        
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        // Get user role for filtering
        $userInfo = SharedManager::getUser();
        $user_id = $userInfo['GID'] ?? null;
        $userRole = 'user';
        $isSupervisor = false;
        $supervisorDepartments = [];
        
        $pdoManager = new PDOManager('spectra_db');
        
        if (in_array(20, $userInfo["Modules"])) {
            $userRole = 'admin';
        } elseif (in_array(21, $userInfo["Modules"])) {
            $userRole = 'supervisor';
            
            // **UPDATED: Check if user GID exists in supervisor field (handles comma-separated)**
            $supervisorCheckQuery = "SELECT DISTINCT er.sub_department 
                         FROM employee_registration er
                         WHERE CONCAT(',', TRIM(REPLACE(er.supervisor, ' ', '')), ',') LIKE :supervisor_id
                         AND er.sub_department IS NOT NULL
                         AND er.sub_department != ''
                         AND (er.status = 'A' OR er.status IS NULL)
                         ORDER BY er.sub_department ASC";
            
            $supervisorCheckResult = $pdoManager->fetchQueryData($supervisorCheckQuery, [':supervisor_id' => '%,' . $user_id . ',%']);
            
            if (isset($supervisorCheckResult['data']) && !empty($supervisorCheckResult['data'])) {
                $isSupervisor = true;
                $supervisorDepartments = array_column($supervisorCheckResult['data'], 'sub_department');
                
                error_log("✅ Supervisor {$user_id} validated - Departments: " . implode(', ', $supervisorDepartments));
            } else {
                error_log("❌ User {$user_id} is not a valid supervisor - No employees found");
                
                // Return empty result for invalid supervisor
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'data' => [],
                    'filters' => [
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                        'sub_department' => $subDepartment,
                        'group_type' => $groupType,
                        'shift_type' => $shiftType,
                        'current_date' => $currentDate,
                        'current_time' => $currentTime,
                        'user_role' => $userRole,
                        'is_valid_supervisor' => false
                    ],
                    'record_count' => 0,
                    'message' => 'You are not a valid supervisor. No employees found under supervision.',
                    'auto_generated' => false,
                    'current_date_filter' => false
                ]);
                exit;
            }
        }
        
        // If no date range is provided, fetch allocations where current date falls within the range
        if (empty($fromDate) || empty($toDate)) {
            $fromDate = $currentDate;
            $toDate = $currentDate;
            $useCurrentDateFilter = true;
        } else {
            $useCurrentDateFilter = false;
        }
        
        // **BUILD THE QUERY - FETCH SHIFT ALLOCATIONS**
        if ($useCurrentDateFilter) {
            // Fetch allocations where current date falls within shift_from and shift_to
            $query = "SELECT DISTINCT
                        er.sub_department,
                        er.group_type,
                        er.shift_type,
                        er.shift_from,
                        er.shift_to
                      FROM employee_registration er
                      WHERE (er.status = 'A' OR er.status IS NULL)
                      AND er.shift_from IS NOT NULL 
                      AND er.shift_to IS NOT NULL
                      AND :current_date BETWEEN er.shift_from AND er.shift_to";
            
            $params = [
                ':current_date' => $currentDate
            ];
        } else {
            // Fetch allocations within the specified date range
            $query = "SELECT DISTINCT
                        er.sub_department,
                        er.group_type,
                        er.shift_type,
                        er.shift_from,
                        er.shift_to
                      FROM employee_registration er
                      WHERE (er.status = 'A' OR er.status IS NULL)
                      AND er.shift_from IS NOT NULL 
                      AND er.shift_to IS NOT NULL
                      AND er.shift_from <= :to_date 
                      AND er.shift_to >= :from_date";
            
            $params = [
                ':from_date' => $fromDate,
                ':to_date' => $toDate
            ];
        }
        
        // **UPDATED: Add role-based filtering for supervisors**
        if ($userRole === 'supervisor' && $isSupervisor && !empty($supervisorDepartments)) {
            // Supervisor: Filter by their departments only
            $deptPlaceholders = [];
            foreach ($supervisorDepartments as $index => $dept) {
                $placeholder = ':dept_' . $index;
                $deptPlaceholders[] = $placeholder;
                $params[$placeholder] = $dept;
            }
            
            $query .= " AND er.sub_department IN (" . implode(',', $deptPlaceholders) . ")";
            
            error_log("🔒 Supervisor {$user_id} query filtered to departments: " . implode(', ', $supervisorDepartments));
        }
        
        // **ADD OPTIONAL FILTERS**
        if (!empty($subDepartment)) {
            // For supervisor, verify the department is in their list
            if ($userRole === 'supervisor' && $isSupervisor && !in_array($subDepartment, $supervisorDepartments)) {
                throw new Exception("Access denied: You don't have permission to view this department");
            }
            
            $query .= " AND er.sub_department = :sub_department";
            $params[':sub_department'] = $subDepartment;
        }
        
        if (!empty($groupType)) {
            $query .= " AND er.group_type = :group_type";
            $params[':group_type'] = $groupType;
        }
        
        if (!empty($shiftType)) {
            $query .= " AND er.shift_type = :shift_type";
            $params[':shift_type'] = $shiftType;
        }
        
        // **GROUP AND ORDER RESULTS**
        $query .= " GROUP BY er.sub_department, er.group_type, er.shift_type, er.shift_from, er.shift_to
                    ORDER BY er.sub_department ASC, er.shift_type ASC, er.group_type ASC";
        
        error_log("📊 Executing query with params: " . json_encode($params));
        
        // Execute query using PDOManager
        $result = $pdoManager->fetchQueryData($query, $params);
        
        if (!$result || !isset($result['data']) || empty($result['data'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [],
                'filters' => [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'sub_department' => $subDepartment,
                    'group_type' => $groupType,
                    'shift_type' => $shiftType,
                    'current_date' => $currentDate,
                    'current_time' => $currentTime,
                    'user_role' => $userRole,
                    'is_valid_supervisor' => $isSupervisor
                ],
                'record_count' => 0,
                'message' => 'No shift allocations found',
                'auto_generated' => $useCurrentDateFilter,
                'current_date_filter' => $useCurrentDateFilter
            ]);
            exit;
        }
        
        $allocations = $result['data'];
        
        // **PROCESS THE DATA TO CREATE A DETAILED REPORT**
        $reportData = [];
        
        foreach ($allocations as $allocation) {
            // Format dates for display
            $shiftFromFormatted = !empty($allocation['shift_from']) 
                ? date('m/d/Y', strtotime($allocation['shift_from'])) 
                : 'N/A';
            $shiftToFormatted = !empty($allocation['shift_to']) 
                ? date('m/d/Y', strtotime($allocation['shift_to'])) 
                : 'N/A';
            
            // **FETCH EMPLOYEES USING COMMON FUNCTION**
            $employees = [];
            if ($includeEmployees === 'true') {
                $employeeResult = $this->getEmployeesUnderSupervision(
                    $pdoManager,
                    $allocation['sub_department'],
                    $allocation['group_type'],
                    $allocation['shift_type'],
                    $allocation['shift_from'],
                    $allocation['shift_to']
                );
                
                if ($employeeResult['status'] === 'success') {
                    $employees = $employeeResult['data'];
                    error_log("✅ Fetched " . count($employees) . " employees for {$allocation['sub_department']} - Shift {$allocation['shift_type']}");
                } else {
                    error_log("⚠️ Failed to fetch employees: " . $employeeResult['message']);
                }
            }
            
            // Add to report data
            $reportData[] = [
                'sub_department' => $allocation['sub_department'],
                'group_type' => $allocation['group_type'],
                'shift_type' => $allocation['shift_type'],
                'shift_from' => $allocation['shift_from'],
                'shift_to' => $allocation['shift_to'],
                'shift_from_formatted' => $shiftFromFormatted,
                'shift_to_formatted' => $shiftToFormatted,
                'employees' => $employees,
                'employee_count' => count($employees)
            ];
        }
        
        error_log("📈 Report generated with " . count($reportData) . " shift allocations");
        
        // **RETURN SUCCESS RESPONSE**
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $reportData,
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'sub_department' => $subDepartment,
                'group_type' => $groupType,
                'shift_type' => $shiftType,
                'current_date' => $currentDate,
                'current_time' => $currentTime,
                'include_employees' => $includeEmployees,
                'user_role' => $userRole,
                'is_valid_supervisor' => $isSupervisor,
                'supervisor_departments' => $supervisorDepartments
            ],
            'record_count' => count($reportData),
            'auto_generated' => $useCurrentDateFilter,
            'current_date_filter' => $useCurrentDateFilter
        ]);
        
    } catch (Exception $e) {
        error_log("❌ getShiftAllocations error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Error: " . $e->getMessage(),
            'data' => [],
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    exit;
}

private function getEmployeesUnderSupervision($pdoManager = null, $subDept = null, $groupType = null, $shiftType = null, $shiftFrom = null, $shiftTo = null) {
    try {
        if (!$pdoManager) {
            $pdoManager = new PDOManager('spectra_db');
        }
        
        $userInfo = SharedManager::getUser();
        $user_id = $userInfo['GID'] ?? null;

        if (!$user_id) {
            return [
                'status' => 'error',
                'message' => 'User ID not found',
                'data' => [],
                'role' => 'unknown'
            ];
        }

        // **DETERMINE USER ROLE**
        $userRole = 'user';
        $isSupervisor = false;
        
        if (in_array(20, $userInfo["Modules"])) {
            $userRole = 'admin';
        } elseif (in_array(21, $userInfo["Modules"])) {
            $userRole = 'supervisor';
            
            // **VERIFY IF USER GID EXISTS IN SUPERVISOR FIELD**
            // Supervisor field can be: "Z002ENYT" or "Z002ENYT,Z004KYYZ" (comma-separated)
           $supervisorCheckQuery = "SELECT COUNT(*) as supervisor_count 
                         FROM employee_registration 
                         WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                         AND (status = 'A' OR status IS NULL)";
            
            $supervisorCheckResult = $pdoManager->fetchQueryData($supervisorCheckQuery, [':supervisor_id' => '%,' . $user_id . ',%']);
            
            if (isset($supervisorCheckResult['data']) && !empty($supervisorCheckResult['data'])) {
                $supervisorCount = $supervisorCheckResult['data'][0]['supervisor_count'] ?? 0;
                $isSupervisor = ($supervisorCount > 0);
            }
            
            if (!$isSupervisor) {
                error_log("❌ User {$user_id} is not a valid supervisor - No employees found");
                return [
                    'status' => 'error',
                    'message' => 'User is not a valid supervisor. No employees found under supervision.',
                    'data' => [],
                    'role' => 'supervisor',
                    'is_valid_supervisor' => false
                ];
            }
            
            error_log("✅ Supervisor {$user_id} validated successfully");
        }

        // **ROLE-BASED DATA FETCHING**
        if ($userRole === 'admin') {
            // ADMIN: Fetch ALL employees
            $query = "SELECT *
                      FROM employee_registration
                      WHERE (status = 'A' OR status IS NULL)";
            
            $params = [];
            
            // Optional filters for admin
            if ($subDept) {
                $query .= " AND sub_department = :sub_department";
                $params[':sub_department'] = $subDept;
            }
            
            if ($groupType) {
                $query .= " AND group_type = :group_type";
                $params[':group_type'] = $groupType;
            }
            
            if ($shiftType) {
                $query .= " AND shift_type = :shift_type";
                $params[':shift_type'] = $shiftType;
            }
            
            if ($shiftFrom && $shiftTo) {
                $query .= " AND shift_from <= :shift_to AND shift_to >= :shift_from";
                $params[':shift_from'] = $shiftFrom;
                $params[':shift_to'] = $shiftTo;
            }
            
            $query .= " ORDER BY name ASC";
            
            $result = $pdoManager->fetchQueryData($query, $params);
            
            $employees = isset($result['data']) ? $result['data'] : [];

            return [
                'status' => 'success',
                'message' => 'All employees fetched successfully',
                'role' => 'admin',
                'is_valid_supervisor' => true,
                'data' => $employees,
                'count' => count($employees)
            ];

        } elseif ($userRole === 'supervisor' && $isSupervisor) {
            // SUPERVISOR: Fetch ONLY employees where current user is their supervisor
            // This checks if the current user's GID is in the supervisor field
            
            $query = "SELECT *
                        FROM employee_registration
                        WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                        AND (status = 'A' OR status IS NULL)";
            
            $params = [':supervisor_id' => '%,' . $user_id . ',%'];
            
            // Optional filters for supervisor
            if ($subDept) {
                $query .= " AND sub_department = :sub_department";
                $params[':sub_department'] = $subDept;
            }
            
            if ($groupType) {
                $query .= " AND group_type = :group_type";
                $params[':group_type'] = $groupType;
            }
            
            if ($shiftType) {
                $query .= " AND shift_type = :shift_type";
                $params[':shift_type'] = $shiftType;
            }
            
            if ($shiftFrom && $shiftTo) {
                $query .= " AND shift_from <= :shift_to AND shift_to >= :shift_from";
                $params[':shift_from'] = $shiftFrom;
                $params[':shift_to'] = $shiftTo;
            }

            $query .= " ORDER BY name ASC";

            $result = $pdoManager->fetchQueryData($query, $params);
            
            $employees = isset($result['data']) ? $result['data'] : [];

            error_log("📊 Supervisor {$user_id} - Found " . count($employees) . " employees under supervision");

            return [
                'status' => 'success',
                'message' => 'Employees under supervision fetched successfully',
                'role' => 'supervisor',
                'is_valid_supervisor' => true,
                'supervisor_id' => $user_id,
                'data' => $employees,
                'count' => count($employees)
            ];

        } else {
            // REGULAR USER: Fetch only their own data
            $query = "SELECT *
                      FROM employee_registration
                      WHERE gid = :user_id
                      AND (status = 'A' OR status IS NULL)
                      LIMIT 1";

            $params = [':user_id' => $user_id];

            $result = $pdoManager->fetchQueryData($query, $params);
            
            $employees = isset($result['data']) ? $result['data'] : [];

            if (empty($employees)) {
                return [
                    'status' => 'error',
                    'message' => 'Your employee record not found',
                    'data' => [],
                    'role' => 'user',
                    'is_valid_supervisor' => false
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Your employee data fetched successfully',
                'role' => 'user',
                'is_valid_supervisor' => false,
                'data' => $employees,
                'count' => 1
            ];
        }

    } catch (Exception $e) {
        error_log("getEmployeesUnderSupervision error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Error fetching employees: ' . $e->getMessage(),
            'data' => [],
            'role' => 'unknown',
            'is_valid_supervisor' => false
        ];
    }
}

private function getEmployeesForShift($subDepartment, $groupType, $shiftType, $shiftFrom, $shiftTo, $pdoManager) {
    try {
        // Query to get employees for this specific shift allocation
        $employeeQuery = "SELECT 
                            er.id,
                            er.gid,
                            er.name as employee_name,
                            er.sub_department,
                            er.group_type,
                            er.shift_type
                          FROM employee_registration er
                          WHERE er.sub_department = :sub_department
                          AND er.group_type = :group_type
                          AND er.shift_type = :shift_type
                          AND er.shift_from = :shift_from
                          AND er.shift_to = :shift_to
                          AND (er.status = 'A' OR er.status IS NULL)
                          ORDER BY er.name ASC";
        
        $employeeParams = [
            ':sub_department' => $subDepartment,
            ':group_type' => $groupType,
            ':shift_type' => $shiftType,
            ':shift_from' => $shiftFrom,
            ':shift_to' => $shiftTo
        ];
        
        $employeeResult = $pdoManager->fetchQueryData($employeeQuery, $employeeParams);
        
        if ($employeeResult && isset($employeeResult['data']) && !empty($employeeResult['data'])) {
            return $employeeResult['data'];
        }
        
        return [];
        
    } catch (Exception $e) {
        error_log("getEmployeesForShift error: " . $e->getMessage());
        return [];
    }
}

public function getGIDSuggestions() {
    $searchTerm = $_POST['searchTerm'] ?? '';
    
    if (empty(trim($searchTerm))) {
        echo json_encode([
            "data" => [],
            "message" => "Search term is required"
        ]);
        return;
    }
    
    $searchTerm = trim($searchTerm);
    
    $query = "SELECT 
                sd.gid AS `key`,
                CONCAT(sd.gid, ' - ', IFNULL(CONCAT(sd.givenName, ' ', sd.surname), 'Unknown')) AS `value`
            FROM scd_details sd
            INNER JOIN (
                SELECT 
                    gid,
                    MAX(CONCAT(year, LPAD(month, 2, '0'))) AS latestPeriod
                FROM scd_details
                GROUP BY gid
            ) latest 
                ON sd.gid = latest.gid 
                AND CONCAT(sd.year, LPAD(sd.month, 2, '0')) = latest.latestPeriod
            WHERE 
                (sd.gid LIKE :searchTerm 
                OR CONCAT(sd.givenName, ' ', sd.surname) LIKE :nameTerm)
                AND sd.gid IS NOT NULL
                AND sd.gid != ''
                AND sd.status = 'A'
            ORDER BY sd.gid ASC
            LIMIT 50;";
    
    $params = [
        ":searchTerm" => "%$searchTerm%",
        ":nameTerm" => "%$searchTerm%"
    ];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    $response = [
        "data" => $result["data"] ?? [],
        "count" => count($result["data"] ?? []),
        "searchTerm" => $searchTerm
    ];
    
    if (empty($result["data"])) {
        $response["message"] = "No active users found (status 'A' only)";
    }
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

        
    public function listGIDs($limit = 0) {
    $query = "SELECT 
                sd.gid AS `key`,
                CONCAT(sd.gid, ' - ', IFNULL(CONCAT(sd.givenName, ' ', sd.surname), 'Unknown')) AS `value`
              FROM scd_details sd
              INNER JOIN (
                  SELECT 
                      gid,
                      MAX(CONCAT(year, LPAD(month, 2, '0'))) AS latestPeriod
                  FROM scd_details
                  GROUP BY gid
              ) latest 
                  ON sd.gid = latest.gid 
                  AND CONCAT(sd.year, LPAD(sd.month, 2, '0')) = latest.latestPeriod
              WHERE sd.gid IS NOT NULL
              AND sd.gid != ''
              AND sd.status = 'A'
              ORDER BY sd.gid ASC";
    
    if ($limit > 0) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    $response = [
        "data" => $result["data"] ?? [],
        "count" => count($result["data"] ?? []),
        "message" => "Active users only (status 'A')"
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

    public function getGIDDetails() {
        $gid = $_POST['gid'] ?? '';
        
        // Initialize response structure
        $response = [
            "success" => false,
            "existingUser" => false,
            "userData" => null,
            "dropdownOptions" => [
                "departments" => [],
                "roles" => [],
                "managers" => [],
                "sponsor" => []
            ],
            "debug" => [
                "departmentsCount" => 0,
                "rolesCount" => 0,
                "managersCount" => 0,
                "sponsorCount" => 0,
                "gid" => $gid,
                "originalLineManagerGID" => null,
                "originalIncompanyManagerGID" => null,
                "incompanyManagerName" => null,
                "lineManagerName" => null,
                "sponsorName" => null
            ]
        ];
        
        // Validate GID input
        if (empty($gid)) {
            $response["error"] = "GID is required";
            echo json_encode($response, JSON_THROW_ON_ERROR);
            return;
        }
        
        // Get basic user data
        $userData = $this->getUserData($gid);
        if ($userData === false) {
            $response["error"] = "Failed to fetch user data";
            echo json_encode($response, JSON_THROW_ON_ERROR);
            return;
        }
        
        $existingUser = !empty($userData);
        $response["existingUser"] = $existingUser;
        
        if ($userData) {
            // Process manager hierarchy
            $userData = $this->processManagerHierarchy($userData);
            if ($userData === false) {
                $response["error"] = "Failed to process manager hierarchy";
                echo json_encode($response, JSON_THROW_ON_ERROR);
                return;
            }
            
            $response["userData"] = $userData;
            $response["debug"]["originalLineManagerGID"] = $userData['line_manager_gid'];
            $response["debug"]["originalIncompanyManagerGID"] = $userData['in_company_manager_gid'];
            $response["debug"]["sponsorGID"] = $userData['sponsor_gid'];
            $response["debug"]["incompanyManagerName"] = $userData['in_company_manager_name'];
            $response["debug"]["lineManagerName"] = $userData['line_manager_name'];
            $response["debug"]["sponsorName"] = $userData['sponsor_name'];
        }
        
        // Get dropdown options
        $departments = $this->getDepartments();
        $roles = $this->getRoles();
        $managers = $this->getManagers();
        $sponsor = $this->getSponsors();
        
        if ($departments === false || $roles === false || $managers === false) {
            $response["error"] = "Failed to fetch dropdown options";
            echo json_encode($response, JSON_THROW_ON_ERROR);
            return;
        }
        
        $response["dropdownOptions"]["departments"] = $departments;
        $response["dropdownOptions"]["roles"] = $roles;
        $response["dropdownOptions"]["managers"] = $managers;
        $response["dropdownOptions"]["sponsor"] = $sponsor;
        $response["debug"]["departmentsCount"] = count($departments);
        $response["debug"]["rolesCount"] = count($roles);
        $response["debug"]["managersCount"] = count($managers);
        $response["debug"]["sponsorCount"] = count($sponsor);
        
        $response["success"] = true;
        echo json_encode($response, JSON_THROW_ON_ERROR);
    }

    public function getDepartment() {
    $departmentsQuery = "
        SELECT DISTINCT department
        FROM scd_details
        WHERE department IS NOT NULL
        AND TRIM(department) <> ''
        ORDER BY department
    ";
    
    $departmentsResult = DbManager::fetchPDOQueryData('spectra_db', $departmentsQuery, []);
    
    if ($departmentsResult === false) {
        return [];
    }
    
    return !empty($departmentsResult["data"])
        ? array_column($departmentsResult["data"], 'department')
        : [];
}

public function searchEmployeesByGIDOrName() {
    $searchTerm = $_POST['searchTerm'] ?? '';
    
    if (empty(trim($searchTerm))) {
        echo json_encode([
            "data" => [],
            "message" => "Search term is required"
        ]);
        return;
    }
    
    $searchTerm = trim($searchTerm);
    
    $query = "SELECT 
                er.gid AS `key`,
                CONCAT(er.gid, ' - ', IFNULL(er.name, 'Unknown')) AS `value`
            FROM employee_registration er
            WHERE 
                (er.gid LIKE :searchTerm 
                OR er.name LIKE :nameTerm)
                AND er.gid IS NOT NULL
                AND er.gid != ''
                AND er.status = 'A'
            ORDER BY er.gid ASC
            LIMIT 50;";
    
    $params = [
        ":searchTerm" => "%$searchTerm%",
        ":nameTerm" => "%$searchTerm%"
    ];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    $response = [
        "data" => $result["data"] ?? [],
        "count" => count($result["data"] ?? []),
        "searchTerm" => $searchTerm
    ];
    
    if (empty($result["data"])) {
        $response["message"] = "No active users found (status 'A' only)";
    }
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function fetchAllActiveEmployeesList($limit = 0) {
    $query = "SELECT 
                er.gid AS `key`,
                CONCAT(er.gid, ' - ', IFNULL(er.name, 'Unknown')) AS `value`
              FROM employee_registration er
              WHERE er.gid IS NOT NULL
              AND er.gid != ''
              AND er.status = 'A'
              ORDER BY er.gid ASC";
    
    if ($limit > 0) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    $response = [
        "data" => $result["data"] ?? [],
        "count" => count($result["data"] ?? []),
        "message" => "Active users only (status 'A')"
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function retrieveEmployeeCompleteProfile() {
    $gid = $_POST['gid'] ?? '';
    
    // Initialize response structure
    $response = [
        "success" => false,
        "employeeExists" => false,
        "profileData" => null,
        "filterOptions" => [
            "departmentList" => [],
            "roleList" => [],
            "managerList" => [],
            "sponsorList" => []
        ],
        "metadata" => [
            "totalDepartments" => 0,
            "totalRoles" => 0,
            "totalManagers" => 0,
            "totalSponsors" => 0,
            "requestedGID" => $gid,
            "lineManagerGID" => null,
            "companyManagerGID" => null,
            "sponsorGID" => null,
            "lineManagerFullName" => null,
            "companyManagerFullName" => null,
            "sponsorFullName" => null
        ]
    ];
    
    // Validate GID input
    if (empty($gid)) {
        $response["error"] = "GID is required";
        echo json_encode($response, JSON_THROW_ON_ERROR);
        return;
    }
    
    // Get basic user data
    $employeeData = $this->fetchEmployeeBasicInfo($gid);
    if ($employeeData === false) {
        $response["error"] = "Failed to fetch employee data";
        echo json_encode($response, JSON_THROW_ON_ERROR);
        return;
    }
    
    $employeeExists = !empty($employeeData);
    $response["employeeExists"] = $employeeExists;
    
    if ($employeeData) {
        // Process manager hierarchy
        $employeeData = $this->resolveManagerHierarchyNames($employeeData);
        if ($employeeData === false) {
            $response["error"] = "Failed to process manager hierarchy";
            echo json_encode($response, JSON_THROW_ON_ERROR);
            return;
        }
        
        $response["profileData"] = $employeeData;
        $response["metadata"]["lineManagerGID"] = $employeeData['line_manager_gid'] ?? null;
        $response["metadata"]["companyManagerGID"] = $employeeData['in_company_manager_gid'] ?? null;
        $response["metadata"]["sponsorGID"] = $employeeData['sponsor_gid'] ?? null;
        $response["metadata"]["lineManagerFullName"] = $employeeData['line_manager_name'] ?? null;
        $response["metadata"]["companyManagerFullName"] = $employeeData['in_company_manager_name'] ?? null;
        $response["metadata"]["sponsorFullName"] = $employeeData['sponsor_name'] ?? null;
    }
    
    // Get filter options
    $departments = $this->fetchDepartmentsList();
    $roles = $this->fetchRolesList();
    $managers = $this->fetchManagersList();
    $sponsors = $this->fetchSponsorsList();
    
    if ($departments === false || $roles === false || $managers === false) {
        $response["error"] = "Failed to fetch filter options";
        echo json_encode($response, JSON_THROW_ON_ERROR);
        return;
    }
    
    $response["filterOptions"]["departmentList"] = $departments;
    $response["filterOptions"]["roleList"] = $roles;
    $response["filterOptions"]["managerList"] = $managers;
    $response["filterOptions"]["sponsorList"] = $sponsors;
    $response["metadata"]["totalDepartments"] = count($departments);
    $response["metadata"]["totalRoles"] = count($roles);
    $response["metadata"]["totalManagers"] = count($managers);
    $response["metadata"]["totalSponsors"] = count($sponsors);
    
    $response["success"] = true;
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function fetchAvailableDepartments() {
    $departmentsQuery = "
        SELECT DISTINCT department
        FROM employee_registration
        WHERE department IS NOT NULL
        AND TRIM(department) <> ''
        AND status = 'A'
        ORDER BY department ASC
    ";
    
    $departmentsResult = DbManager::fetchPDOQueryData('spectra_db', $departmentsQuery, []);
    
    if ($departmentsResult === false) {
        return [];
    }
    
    return !empty($departmentsResult["data"])
        ? array_column($departmentsResult["data"], 'department')
        : [];
}

// ==================== HELPER METHODS ====================

public function fetchEmployeeBasicInfo($gid) {
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.status,
                er.line_manager_gid,
                er.in_company_manager_gid,
                er.sponsor_gid
            FROM employee_registration er
            WHERE er.gid = :gid
            AND er.status = 'A'
            LIMIT 1;";
    
    $params = [":gid" => $gid];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    if ($result === false || empty($result["data"])) {
        return false;
    }
    
    return $result["data"][0] ?? false;
}

public function resolveManagerHierarchyNames($employeeData) {
    // Get line manager name
    if (!empty($employeeData['line_manager_gid'])) {
        $lineManagerQuery = "SELECT name FROM employee_registration WHERE gid = :gid AND status = 'A' LIMIT 1;";
        $lineManagerResult = DbManager::fetchPDOQueryData('spectra_db', $lineManagerQuery, [":gid" => $employeeData['line_manager_gid']]);
        $employeeData['line_manager_name'] = !empty($lineManagerResult["data"]) ? $lineManagerResult["data"][0]['name'] : 'Unknown';
    } else {
        $employeeData['line_manager_name'] = null;
    }
    
    // Get in-company manager name
    if (!empty($employeeData['in_company_manager_gid'])) {
        $inCompanyManagerQuery = "SELECT name FROM employee_registration WHERE gid = :gid AND status = 'A' LIMIT 1;";
        $inCompanyManagerResult = DbManager::fetchPDOQueryData('spectra_db', $inCompanyManagerQuery, [":gid" => $employeeData['in_company_manager_gid']]);
        $employeeData['in_company_manager_name'] = !empty($inCompanyManagerResult["data"]) ? $inCompanyManagerResult["data"][0]['name'] : 'Unknown';
    } else {
        $employeeData['in_company_manager_name'] = null;
    }
    
    // Get sponsor name
    if (!empty($employeeData['sponsor_gid'])) {
        $sponsorQuery = "SELECT name FROM employee_registration WHERE gid = :gid AND status = 'A' LIMIT 1;";
        $sponsorResult = DbManager::fetchPDOQueryData('spectra_db', $sponsorQuery, [":gid" => $employeeData['sponsor_gid']]);
        $employeeData['sponsor_name'] = !empty($sponsorResult["data"]) ? $sponsorResult["data"][0]['name'] : 'Unknown';
    } else {
        $employeeData['sponsor_name'] = null;
    }
    
    return $employeeData;
}

public function fetchDepartmentsList() {
    $query = "SELECT DISTINCT department
              FROM employee_registration
              WHERE department IS NOT NULL
              AND TRIM(department) <> ''
              AND status = 'A'
              ORDER BY department ASC;";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    if ($result === false) {
        return false;
    }
    
    return $result["data"] ?? [];
}

public function fetchRolesList() {
    $query = "SELECT DISTINCT role
              FROM employee_registration
              WHERE role IS NOT NULL
              AND TRIM(role) <> ''
              AND status = 'A'
              ORDER BY role ASC;";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    if ($result === false) {
        return false;
    }
    
    return $result["data"] ?? [];
}

public function fetchManagersList() {
    $query = "SELECT 
                gid AS `key`,
                CONCAT(gid, ' - ', IFNULL(name, 'Unknown')) AS `value`
              FROM employee_registration
              WHERE gid IS NOT NULL
              AND gid != ''
              AND status = 'A'
              ORDER BY gid ASC;";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    if ($result === false) {
        return false;
    }
    
    return $result["data"] ?? [];
}

public function fetchSponsorsList() {
    $query = "SELECT 
                gid AS `key`,
                CONCAT(gid, ' - ', IFNULL(name, 'Unknown')) AS `value`
              FROM employee_registration
              WHERE gid IS NOT NULL
              AND gid != ''
              AND status = 'A'
              ORDER BY gid ASC;";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    if ($result === false) {
        return false;
    }
    
    return $result["data"] ?? [];
}

// ==================== ADDITIONAL UTILITY FUNCTIONS ====================

public function retrieveAllActiveStaff() {
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.status
            FROM employee_registration er
            WHERE er.status = 'A'
            ORDER BY er.name ASC;";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    $response = [
        "success" => true,
        "staffList" => $result["data"] ?? [],
        "totalCount" => count($result["data"] ?? [])
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function filterStaffByDepartment() {
    $department = $_POST['department'] ?? '';
    
    if (empty(trim($department))) {
        echo json_encode([
            "success" => false,
            "message" => "Department is required"
        ]);
        return;
    }
    
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.status
            FROM employee_registration er
            WHERE er.department = :department
            AND er.status = 'A'
            ORDER BY er.name ASC;";
    
    $params = [":department" => $department];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    $response = [
        "success" => true,
        "selectedDepartment" => $department,
        "staffList" => $result["data"] ?? [],
        "totalCount" => count($result["data"] ?? [])
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function filterStaffByRole() {
    $role = $_POST['role'] ?? '';
    
    if (empty(trim($role))) {
        echo json_encode([
            "success" => false,
            "message" => "Role is required"
        ]);
        return;
    }
    
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.status
            FROM employee_registration er
            WHERE er.role = :role
            AND er.status = 'A'
            ORDER BY er.name ASC;";
    
    $params = [":role" => $role];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    $response = [
        "success" => true,
        "selectedRole" => $role,
        "staffList" => $result["data"] ?? [],
        "totalCount" => count($result["data"] ?? [])
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function advancedStaffSearch() {
    $searchTerm = $_POST['searchTerm'] ?? '';
    
    if (empty(trim($searchTerm))) {
        echo json_encode([
            "success" => false,
            "message" => "Search term is required"
        ]);
        return;
    }
    
    $searchTerm = trim($searchTerm);
    
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.status
            FROM employee_registration er
            WHERE (er.gid LIKE :searchTerm 
                OR er.name LIKE :searchTerm
                OR er.email LIKE :searchTerm)
            AND er.status = 'A'
            ORDER BY er.name ASC
            LIMIT 100;";
    
    $params = [":searchTerm" => "%$searchTerm%"];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    $response = [
        "success" => true,
        "searchQuery" => $searchTerm,
        "staffList" => $result["data"] ?? [],
        "totalCount" => count($result["data"] ?? [])
    ];
    
    if (empty($result["data"])) {
        $response["message"] = "No employees found matching your search";
    }
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function retrieveExtendedEmployeeInformation() {
    $gid = $_POST['gid'] ?? '';
    
    if (empty($gid)) {
        echo json_encode([
            "success" => false,
            "message" => "GID is required"
        ]);
        return;
    }
    
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.status,
                er.line_manager_gid,
                er.in_company_manager_gid,
                er.sponsor_gid,
                lm.name AS line_manager_name,
                icm.name AS in_company_manager_name,
                sp.name AS sponsor_name
            FROM employee_registration er
            LEFT JOIN employee_registration lm ON er.line_manager_gid = lm.gid
            LEFT JOIN employee_registration icm ON er.in_company_manager_gid = icm.gid
            LEFT JOIN employee_registration sp ON er.sponsor_gid = sp.gid
            WHERE er.gid = :gid
            AND er.status = 'A'
            LIMIT 1;";
    
    $params = [":gid" => $gid];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    if ($result === false || empty($result["data"])) {
        echo json_encode([
            "success" => false,
            "message" => "Employee not found"
        ]);
        return;
    }
    
    $response = [
        "success" => true,
        "employeeInformation" => $result["data"][0]
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function fetchStaffHierarchyStructure() {
    $managerGID = $_POST['managerGID'] ?? '';
    
    if (empty($managerGID)) {
        echo json_encode([
            "success" => false,
            "message" => "Manager GID is required"
        ]);
        return;
    }
    
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.line_manager_gid
            FROM employee_registration er
            WHERE (er.line_manager_gid = :managerGID 
                OR er.in_company_manager_gid = :managerGID)
            AND er.status = 'A'
            ORDER BY er.name ASC;";
    
    $params = [":managerGID" => $managerGID];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    $response = [
        "success" => true,
        "managerGID" => $managerGID,
        "subordinates" => $result["data"] ?? [],
        "totalSubordinates" => count($result["data"] ?? [])
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

public function generateEmployeeReport() {
    $query = "SELECT 
                er.gid,
                er.name,
                er.email,
                er.department,
                er.role,
                er.status,
                lm.name AS line_manager_name,
                COUNT(*) OVER (PARTITION BY er.department) AS dept_count
            FROM employee_registration er
            LEFT JOIN employee_registration lm ON er.line_manager_gid = lm.gid
            WHERE er.status = 'A'
            ORDER BY er.department, er.name ASC;";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, []);
    
    $response = [
        "success" => true,
        "reportData" => $result["data"] ?? [],
        "totalEmployees" => count($result["data"] ?? []),
        "generatedAt" => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
}


    public function submitRegistration() {
    try {
        $userInfo = SharedManager::getUser();
        
        $user_id = $userInfo['GID'] ?? null;
        $username = $userInfo['FullName'] ?? null;

        // Validate session data
        if (empty($user_id) || empty($username)) {
            error_log("User info from SharedManager: " . print_r($userInfo, true));
            throw new Exception("User session not found. Please login again.");
        }

        // Update required fields array (excluding manager fields)
        $requiredFields = [
            'gid', 
            'name', 
            'role', 
            'group_type',
            'employment_type',
            'joined',
            'supervisor'
        ];
        
        // Validate required fields
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required fields cannot be empty");
            }
        }

        // Ensure at least one of the manager/sponsor fields is filled
        if (
            empty($_POST['in_company_manager']) &&
            empty($_POST['line_manager']) &&
            empty($_POST['sponsor'])
        ) {
            throw new Exception("At least one of In-Company Manager, Line Manager, or Sponsor must be filled.");
        }

        // Check for duplicate GID
        $checkQuery = "SELECT COUNT(*) as count FROM employee_registration WHERE gid = :gid";
        $checkResult = DbManager::fetchPDOQueryData('spectra_db', $checkQuery, [':gid' => $_POST['gid']]);
        
        if ($checkResult['data'][0]['count'] > 0) {
            throw new Exception("This GID is already registered in the system");
        }

        // ✅ Prepare insert query
        $query = "INSERT INTO employee_registration (
                    gid, 
                    name, 
                    department, 
                    sub_department,
                    role,
                    group_type,
                    in_company_manager,
                    line_manager,
                    sponsor,
                    employment_type,
                    joined,
                    grade,
                    supervisor,
                    user_id,
                    username,
                    created_at
                ) VALUES (
                    :gid,
                    :name,
                    :department,
                    :sub_department,
                    :role,
                    :group_type,
                    :in_company_manager,
                    :line_manager,
                    :sponsor,
                    :employment_type,
                    :joined,
                    :grade,
                    :supervisor,
                    :user_id,
                    :username,
                    NOW()
                )";

        $params = [
            ':gid' => $_POST['gid'],
            ':name' => $_POST['name'],
            ':department' => $_POST['department'] ?? null,
            ':sub_department' => $_POST['sub_department'] ?? null,
            ':role' => $_POST['role'],
            ':group_type' => $_POST['group_type'],
            ':in_company_manager' => $_POST['in_company_manager'] ?? null,
            ':line_manager' => $_POST['line_manager'] ?? null,
            ':sponsor' => $_POST['sponsor'] ?? null,
            ':employment_type' => $_POST['employment_type'],
            ':joined' => $_POST['joined'],
            ':grade' => $_POST['grade'],
            ':supervisor' => $_POST['supervisor'],
            ':user_id' => $user_id,      // Z0054M7D
            ':username' => $username     // GEETA RAUTELA
        ];

        $result = DbManager::fetchPDOQuery('spectra_db', $query, $params);

        if ($result === false) {
            throw new Exception("Failed to insert user registration");
        }

        echo json_encode([
            'success' => true,
            'message' => 'User registration successful'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}


public function fetchEmployeeBasicData()
{
    // Check if GID is provided in the request
    if (!isset($_POST['gid']) || empty($_POST['gid'])) {
        echo json_encode([
            'success' => false,
            'message' => 'GID is required'
        ]);
        exit;
    }
    
    $gid = $_POST['gid'];
    
    $sql = "SELECT 
                id, 
                gid, 
                name, 
                department, 
                sub_department, 
                role, 
                group_type, 
                in_company_manager, 
                line_manager, 
                supervisor,
                sponsor, 
                employment_type, 
                joined
            FROM employee_registration 
            WHERE gid = :gid 
            AND (status = 'A' OR status IS NULL)
            LIMIT 1";
            
    $params = [':gid' => $gid];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    // Check if employee was found
    if (empty($result["data"])) {
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found with the provided GID'
        ]);
        exit;
    }
    
    // Get the employee data
    $data = $result["data"][0];

    $rawSupervisorGIDs = trim($data['supervisor']); 
    $finalSupervisorString = ''; 

    if (!empty($rawSupervisorGIDs)) {
        $supervisorGIDs = array_filter(array_map('trim', explode(',', $rawSupervisorGIDs)));

        if (!empty($supervisorGIDs)) {
            $placeholders = implode(',', array_fill(0, count($supervisorGIDs), '?'));

            $nameSql = "SELECT gid, givenName, surname FROM scd_details WHERE gid IN ({$placeholders})";
            
            $nameResult = DbManager::fetchPDOQueryData('spectra_db', $nameSql, $supervisorGIDs);

            $supervisorNameMap = [];
            if (!empty($nameResult['data'])) {
                foreach ($nameResult['data'] as $supData) {
                    $fullNameParts = [];
                    if (!empty($supData['givenName'])) {
                        $fullNameParts[] = trim($supData['givenName']);
                    }
                    if (!empty($supData['surname'])) {
                        $fullNameParts[] = trim($supData['surname']);
                    }
                    $fullName = implode(' ', $fullNameParts);
                    $supervisorNameMap[trim($supData['gid'])] = $fullName;
                }
            }

            $formattedSupervisors = [];
            foreach ($supervisorGIDs as $supGID) {
                $supervisorName = $supervisorNameMap[$supGID] ?? ''; 
                
                if (!empty($supervisorName)) {
                    $formattedSupervisors[] = "{$supervisorName} ({$supGID})";
                } else {
                    $formattedSupervisors[] = $supGID;
                }
            }
            $finalSupervisorString = implode(', ', $formattedSupervisors);
        }
    }
    
    $userData = [
        'user_id' => trim($data['id']),
        'gid' => trim($data['gid']),
        'name' => trim($data['name']),
        'department' => trim($data['department']),
        'sub_department' => trim($data['sub_department']),
        'sub_departmentUniqueNumber' => preg_replace('/[^a-zA-Z0-9 _-]/', '', trim($data['sub_department'])),
        'role' => trim($data['role']),
        'group_type' => trim($data['group_type']),
        'in_company_manager' => trim($data['in_company_manager']),
        'line_manager' => trim($data['line_manager']),
        'supervisor' => $finalSupervisorString, 
        'sponsor' => trim($data['sponsor']),
        'employment_type' => trim($data['employment_type']),
        'joined' => trim($data['joined'])
    ];
    
    $response = [
        'success' => true,
        'userData' => $userData
    ];
    
    echo json_encode($response);
    exit;
}
    
public function updateShiftAllocation() {
    try {
        $userInfo = SharedManager::getUser();
        
        $user_id = $userInfo['GID'] ?? null;
        $username = $userInfo['FullName'] ?? null;

        // Get user role
        $userRole = 'user';
        $isSupervisor = false;
        
        if (in_array(20, $userInfo["Modules"])) {
            $userRole = 'admin';
        } elseif (in_array(21, $userInfo["Modules"])) {
            $userRole = 'supervisor';
        }

        // Validate required fields
        $requiredFields = ['sub_department', 'group_type', 'shift_type', 'from_date_db', 'to_date_db'];

        $sub_department = $_POST['sub_department'] ?? null;
        $group_type = $_POST['group_type'] ?? null;
        $shift_type = $_POST['shift_type'] ?? null;
        $shift_from = $_POST['from_date_db'] ?? null;
        $shift_to = $_POST['to_date_db'] ?? null;

        // Validate all required fields are present
        $missing_fields = [];
        foreach ($requiredFields as $field) {
            $field_name = str_replace('_db', '', $field);
            if (empty($_POST[$field_name]) && empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }

        $pdoManager = new PDOManager('spectra_db');

        // **ROLE-BASED VALIDATION FOR SUPERVISORS**
        if ($userRole === 'supervisor') {
            // **VERIFY IF USER IS A VALID SUPERVISOR**
            $supervisorValidationQuery = "SELECT COUNT(*) as emp_count 
                                            FROM employee_registration 
                                            WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                            AND (status = 'A' OR status IS NULL)";
            
            $supervisorValidationResult = $pdoManager->fetchQueryData($supervisorValidationQuery, [
                ':supervisor_id' => '%,' . $user_id . ',%'
            ]);
            
            if (!isset($supervisorValidationResult['data']) || empty($supervisorValidationResult['data'])) {
                throw new Exception("Access denied: You are not a valid supervisor");
            }
            
            $totalEmpCount = $supervisorValidationResult['data'][0]['emp_count'] ?? 0;
            if ($totalEmpCount === 0) {
                throw new Exception("Access denied: You have no employees under supervision");
            }
            
            // **VERIFY SUPERVISOR HAS EMPLOYEES IN THE SELECTED DEPARTMENT**
            $deptCheckQuery = "SELECT COUNT(*) as dept_emp_count 
                                FROM employee_registration 
                                WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                AND sub_department = :sub_department
                                AND (status = 'A' OR status IS NULL)";
                            
            $deptCheckResult = $pdoManager->fetchQueryData($deptCheckQuery, [
                ':supervisor_id' => '%,' . $user_id . ',%',
                ':sub_department' => $sub_department
            ]);
            
            if (!isset($deptCheckResult['data']) || empty($deptCheckResult['data'])) {
                throw new Exception("Access denied: You don't have permission to update this department");
            }
            
            $deptEmpCount = $deptCheckResult['data'][0]['dept_emp_count'] ?? 0;
            if ($deptEmpCount === 0) {
                throw new Exception("Access denied: You have no employees in the selected department");
            }
            
            $isSupervisor = true;
            
            error_log("✅ Supervisor {$user_id} validated - {$deptEmpCount} employees in {$sub_department}");
        }

        // **FETCH ALL EMPLOYEES MATCHING THE CRITERIA USING COMMON FUNCTION**
        $employeeResult = $this->getEmployeesUnderSupervision(
            $pdoManager,
            $sub_department,
            $group_type,
            null,
            null,
            null
        );
        
        if ($employeeResult['status'] !== 'success') {
            throw new Exception($employeeResult['message']);
        }
        
        $employees = $employeeResult['data'];
        $total_employees = count($employees);
        
        if ($total_employees === 0) {
            throw new Exception("No employees found matching the selected criteria");
        }
        
        $updated_count = 0;
        $no_change_count = 0;
        $history_count = 0;
        $change_details = [];
        $failed_updates = [];

        // **PROCESS EACH EMPLOYEE**
        foreach ($employees as $employee) {
            $emp_id = $employee['id'];
            $emp_gid = $employee['gid'] ?? 'Unknown';
            $emp_name = $employee['name'] ?? 'Unknown';
            
            try {
                // Get current data
                $current_data = $employee;
                
                // Set default status to 'A' if not set
                if (!isset($current_data['status']) || empty($current_data['status'])) {
                    $current_data['status'] = 'A';
                }
                
                // Prepare new data
                $new_shift_type = $shift_type;
                $new_shift_from = $shift_from;
                $new_shift_to = $shift_to;
                
                $old_shift_type = $current_data['shift_type'] ?? null;
                $old_shift_from = $current_data['shift_from'] ?? null;
                $old_shift_to = $current_data['shift_to'] ?? null;
                
                // Check if there's actually a change
                $has_changes = (
                    $old_shift_type != $new_shift_type || 
                    $old_shift_from != $new_shift_from || 
                    $old_shift_to != $new_shift_to
                );
                
                // **ALWAYS INSERT INTO HISTORY TABLE BEFORE UPDATING**
                $insert_sql = "INSERT INTO employee_registration_details (
                                  emp_id, gid, name, department, sub_department, role, group_type, 
                                  in_company_manager, line_manager, sponsor, employment_type, shift_type, 
                                  shift_from, shift_to,
                                  temp_sub_department, temp_group_type, transfer_from_date, transfer_to_date, 
                                  joined, status, user_id, username, created_at
                              ) VALUES (
                                  :emp_id, :gid, :name, :department, :sub_department, :role, :group_type, 
                                  :in_company_manager, :line_manager, :sponsor, :employment_type, :shift_type, 
                                  :shift_from, :shift_to,
                                  :temp_sub_department, :temp_group_type, :transfer_from_date, :transfer_to_date, 
                                  :joined, :status, :user_id, :username, NOW()
                              )";
                              
                $history_params = [
                    ':emp_id' => $emp_id,
                    ':gid' => $current_data['gid'] ?? null,
                    ':name' => $current_data['name'] ?? null,
                    ':department' => $current_data['department'] ?? null,
                    ':sub_department' => $current_data['sub_department'] ?? null,
                    ':role' => $current_data['role'] ?? null,
                    ':group_type' => $current_data['group_type'] ?? null,
                    ':in_company_manager' => $current_data['in_company_manager'] ?? null,
                    ':line_manager' => $current_data['line_manager'] ?? null,
                    ':sponsor' => $current_data['sponsor'] ?? null,
                    ':employment_type' => $current_data['employment_type'] ?? null,
                    ':shift_type' => $current_data['shift_type'] ?? null,
                    ':shift_from' => $current_data['shift_from'] ?? null,
                    ':shift_to' => $current_data['shift_to'] ?? null,
                    ':temp_sub_department' => $current_data['temp_sub_department'] ?? null,
                    ':temp_group_type' => $current_data['temp_group_type'] ?? null,
                    ':joined' => $current_data['joined'] ?? null,
                    ':transfer_from_date' => $current_data['transfer_from_date'] ?? null,
                    ':transfer_to_date' => $current_data['transfer_to_date'] ?? null,
                    ':status' => $current_data['status'],
                    ':user_id' => $user_id,
                    ':username' => $username
                ];
                
                $history_result = $pdoManager->fetchQueryData($insert_sql, $history_params);
                
                if (!$history_result) {
                    error_log("⚠️ Failed to create history record for employee ID: {$emp_id} ({$emp_gid})");
                    $failed_updates[] = [
                        'gid' => $emp_gid,
                        'name' => $emp_name,
                        'reason' => 'Failed to create history record'
                    ];
                    continue; 
                }
                
                $history_count++;
                
                // **UPDATE EMPLOYEE SHIFT DATA IF CHANGES EXIST**
                if ($has_changes) {
                    $update_sql = "UPDATE employee_registration 
                                  SET shift_type = :shift_type,
                                      shift_from = :shift_from,
                                      shift_to = :shift_to,
                                      user_id = :user_id,
                                      username = :username
                                  WHERE id = :id 
                                  AND (status = 'A' OR status IS NULL)";

                    $update_params = [
                        ':shift_type' => $new_shift_type,
                        ':shift_from' => $new_shift_from,
                        ':shift_to' => $new_shift_to,
                        ':user_id' => $user_id,
                        ':username' => $username,
                        ':id' => $emp_id
                    ];

                    $update_result = $pdoManager->fetchQueryData($update_sql, $update_params);
                    
                    if (!$update_result) {
                        error_log("⚠️ Failed to update employee ID: {$emp_id} ({$emp_gid})");
                        $failed_updates[] = [
                            'gid' => $emp_gid,
                            'name' => $emp_name,
                            'reason' => 'Failed to update shift data'
                        ];
                        continue;
                    }
                    
                    $updated_count++;
                    $change_details[] = [
                        'gid' => $emp_gid,
                        'name' => $emp_name,
                        'old_shift' => $old_shift_type,
                        'new_shift' => $new_shift_type,
                        'old_shift_from' => $old_shift_from,
                        'new_shift_from' => $new_shift_from,
                        'old_shift_to' => $old_shift_to,
                        'new_shift_to' => $new_shift_to
                    ];
                    
                    error_log("✅ Updated employee {$emp_gid} ({$emp_name}) - Shift {$old_shift_type} → {$new_shift_type}");
                } else {
                    $no_change_count++;
                    error_log("ℹ️ No changes for employee {$emp_gid} ({$emp_name}) - Already has shift {$new_shift_type}");
                }
                
            } catch (Exception $e) {
                error_log("❌ Error processing employee {$emp_id}: " . $e->getMessage());
                $failed_updates[] = [
                    'gid' => $emp_gid,
                    'name' => $emp_name,
                    'reason' => $e->getMessage()
                ];
                continue;
            }
        }

        // **PREPARE RESPONSE MESSAGE**
        $message = '';
        if ($updated_count > 0) {
            $message = "✅ Successfully updated shift allocation for {$updated_count} employee(s)";
            if ($no_change_count > 0) {
                $message .= " ({$no_change_count} already had this shift configuration)";
            }
        } else if ($no_change_count > 0) {
            $message = "ℹ️ All {$no_change_count} employee(s) already have this shift configuration - no changes needed";
        } else {
            $message = "⚠️ No employees were updated";
        }
        
        if (!empty($failed_updates)) {
            $message .= " - ⚠️ {" . count($failed_updates) . " employee(s) failed}";
        }

        // **RETURN SUCCESS RESPONSE**
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'summary' => [
                'total_employees' => $total_employees,
                'updated_count' => $updated_count,
                'no_change_count' => $no_change_count,
                'failed_count' => count($failed_updates),
                'history_records_created' => $history_count
            ],
            'shift_period' => [
                'from' => $shift_from,
                'to' => $shift_to,
                'group_type' => $group_type
            ],
            'changes' => $change_details,
            'failed_updates' => $failed_updates,
            'updated_by' => [
                'user_id' => $user_id,
                'username' => $username,
                'role' => $userRole,
                'is_supervisor' => $isSupervisor
            ]
        ]);

    } catch (Exception $e) {
        error_log("❌ updateShiftAllocation error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Error: " . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    exit;
}

public function submitAttendanceForDate() {
    try {
        $attendanceData = json_decode($_POST['data'], true);
        $subDepartment = $_POST['sub_department'] ?? '';
        $groupType = $_POST['group_type'] ?? '';
        $date = $_POST['date'] ?? '';
        $preserveOvertime = isset($_POST['preserve_overtime']) && $_POST['preserve_overtime'] === 'true';
        $isSave = isset($_POST['is_save']) && $_POST['is_save'] === 'true';
        
        // ===== GET CURRENT USER INFO =====
        $userInfo = SharedManager::getUser();
        $user_id = $userInfo['GID'] ?? null;
        $username = $userInfo['FullName'] ?? null;
        
        if (empty($attendanceData) || empty($subDepartment) || empty($groupType) || empty($date)) {
            throw new Exception('Missing required data');
        }
        
        $insertedCount = 0;
        $updatedCount = 0;
        
        foreach ($attendanceData as $record) {
            // ===== CHECK IF RECORD EXISTS AND GET OVERTIME DATA =====
            $checkQuery = "SELECT id, overtime_hours, ot_status, status FROM employee_attendance 
                          WHERE gid = :gid AND attendance_date = :attendance_date";
            
            $checkResult = DbManager::fetchPDOQueryData('spectra_db', $checkQuery, [
                ':gid' => $record['gid'],
                ':attendance_date' => $record['date']
            ]);
            
            if (!empty($checkResult['data'])) {
                // ===== UPDATE EXISTING RECORD =====
                $existingOvertimeHours = $checkResult['data'][0]['overtime_hours'];
                $existingOtStatus = $checkResult['data'][0]['ot_status'];
                $existingStatus = $checkResult['data'][0]['status'];
                
                // ===== DETERMINE NEW ATTENDANCE STATUS =====
                // 'save' for draft/saved, 'sub' for submitted
                $newStatus = $isSave ? 'save' : 'sub';
                
                error_log("🔄 GID: {$record['gid']}, Date: {$record['date']} - UPDATING existing record");
                error_log("   Current OT Data: Hours=$existingOvertimeHours, OT_Status=$existingOtStatus");
                error_log("   Current Status: $existingStatus");
                error_log("   New Attendance Status: $newStatus");
                
                // ✅ FIX: PRESERVE overtime_hours and ot_status using COALESCE
                // ✅ FIX: Also preserve status field if it already exists
                $updateQuery = "UPDATE employee_attendance 
                               SET employee_name = :employee_name,
                                   actual_man_hours = :actual_man_hours,
                                   attendance_status = :attendance_status,
                                   status = :status,
                                   overtime_hours = COALESCE(NULLIF(overtime_hours, ''), :existing_overtime_hours),
                                   ot_status = COALESCE(NULLIF(ot_status, ''), :existing_ot_status),
                                   updated_at = NOW()
                               WHERE gid = :gid AND attendance_date = :attendance_date";
                
                $updateParams = [
                    ':employee_name' => $record['employee_name'],
                    ':actual_man_hours' => $record['actual_man_hours'],
                    ':attendance_status' => $record['attendance_status'],
                    ':status' => $newStatus,
                    ':existing_overtime_hours' => $existingOvertimeHours,
                    ':existing_ot_status' => $existingOtStatus,
                    ':gid' => $record['gid'],
                    ':attendance_date' => $record['date']
                ];
                
                $result = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);
                
                if ($result !== false) {
                    $updatedCount++;
                    error_log("✅ UPDATED attendance for GID: {$record['gid']}, Date: {$record['date']}");
                    error_log("   Attendance Status: {$record['attendance_status']} | Submission Status: $newStatus");
                    error_log("   ✅ PRESERVED OT: Hours=$existingOvertimeHours, OT_Status=$existingOtStatus");
                } else {
                    error_log("❌ FAILED to update attendance for GID: {$record['gid']}, Date: {$record['date']}");
                }
            } else {
                // ===== INSERT NEW RECORD =====
                $newStatus = $isSave ? 'save' : 'sub';
                
                error_log("➕ GID: {$record['gid']}, Date: {$record['date']} - INSERTING new record");
                error_log("   Attendance Status: {$record['attendance_status']} | Submission Status: $newStatus");
                error_log("   OT fields: NULL (no overtime data yet)");
                
                $insertQuery = "INSERT INTO employee_attendance 
                               (employee_name, gid, actual_man_hours, attendance_date, attendance_status, 
                                status, overtime_hours, ot_status, created_at)
                               VALUES (:employee_name, :gid, :actual_man_hours, :attendance_date, 
                                       :attendance_status, :status, NULL, NULL, NOW())";
                
                $insertParams = [
                    ':employee_name' => $record['employee_name'],
                    ':gid' => $record['gid'],
                    ':actual_man_hours' => $record['actual_man_hours'],
                    ':attendance_date' => $record['date'],
                    ':attendance_status' => $record['attendance_status'],
                    ':status' => $newStatus
                ];
                
                $result = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);
                
                if ($result !== false) {
                    $insertedCount++;
                    error_log("✅ INSERTED attendance for GID: {$record['gid']}, Date: {$record['date']}");
                    error_log("   Attendance Status: {$record['attendance_status']} | Submission Status: $newStatus");
                    error_log("   OT fields initialized to NULL");
                } else {
                    error_log("❌ FAILED to insert attendance for GID: {$record['gid']}, Date: {$record['date']}");
                }
            }
        }
        
        $actionType = $isSave ? 'saved' : 'submitted';
        $message = "Processed: {$insertedCount} new records inserted, {$updatedCount} records {$actionType}";
        
        error_log("📊 ===== ATTENDANCE SUBMISSION SUMMARY =====");
        error_log("   Inserted: $insertedCount");
        error_log("   Updated: $updatedCount");
        error_log("   Action: $actionType");
        error_log("   ✅ OVERTIME DATA PRESERVED: YES");
        error_log("   ✅ OT_STATUS FIELD PRESERVED: YES");
        error_log("   ✅ TABLES DECOUPLED: YES");
        error_log("=============================================");
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'action_type' => $actionType,
            'status' => $isSave ? 'save' : 'sub',
            'overtime_preserved' => true,
            'ot_status_preserved' => true
        ]);
        
    } catch (Exception $e) {
        error_log("❌ submitAttendanceForDate ERROR: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

public function submitOvertimeForDate() {
    try {
        // ===== VALIDATE INPUT =====
        if (!isset($_POST['data'])) {
            throw new Exception('Missing overtime data parameter');
        }
        
        $overtimeData = json_decode($_POST['data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        $date = $_POST['date'] ?? date('Y-m-d');
        
        if (!is_array($overtimeData)) {
            $overtimeData = [];
        }
        
        $isHoliday = isset($_POST['is_holiday']) ? (int)$_POST['is_holiday'] : 0;
        $isSave = isset($_POST['is_save']) && $_POST['is_save'] === 'true';
        
        // ===== GET CURRENT USER INFO =====
        $userInfo = SharedManager::getUser();
        $current_user_id = $userInfo['GID'] ?? null;
        $userModules = $userInfo['Modules'] ?? [];
        
        // ===== ROLE VALIDATION =====
        $isAdmin = in_array(20, $userModules);
        $isSupervisor = in_array(21, $userModules);
        $isRegularUser = in_array(19, $userModules);
        
        error_log("✅ submitOvertimeForDate - User: $current_user_id | Admin: " . ($isAdmin ? 'YES' : 'NO') . " | Supervisor: " . ($isSupervisor ? 'YES' : 'NO') . " | Regular: " . ($isRegularUser ? 'YES' : 'NO'));
        
        // ===== DETERMINE OT_STATUS (OVERTIME STATUS ONLY) =====
        $newOtStatus = $isSave ? 'save' : 'sub';
        
        error_log("📋 ===== submitOvertimeForDate START =====");
        error_log("   Date: $date");
        error_log("   IsHoliday: $isHoliday");
        error_log("   OT_Status: $newOtStatus");
        error_log("   Action: " . ($isSave ? 'SAVE' : 'SUBMIT'));
        error_log("   User Role: " . ($isAdmin ? 'ADMIN' : ($isSupervisor ? 'SUPERVISOR' : 'REGULAR')));
        
        // ===== HANDLE EMPTY DATA =====
        if (empty($overtimeData)) {
            error_log("⚠️ No overtime data provided, but proceeding with save/submit");
            
            echo json_encode([
                'success' => true,
                'message' => 'Overtime data processed (no records to save)',
                'updated' => 0,
                'holiday' => 0,
                'leave' => 0,
                'zeroEntries' => 0,
                'invalid' => 0,
                'missingNames' => 0,
                'ot_status' => $newOtStatus,
                'action_type' => $isSave ? 'saved' : 'submitted'
            ]);
            return;
        }
        
        // ===== INITIALIZE COUNTERS =====
        $updatedCount = 0;
        $holidayCount = 0;
        $leaveCount = 0;
        $invalidCount = 0;
        $missingNameCount = 0;
        $zeroEntryCount = 0;
        
        // ===== CHECK IF DATE IS A CONFIRMED HOLIDAY =====
        $holidayCheckQuery = "SELECT day_type FROM emp_yearly_holiday WHERE date = :date AND day_type = 'holiday'";
        $holidayCheckResult = DbManager::fetchPDOQueryData('spectra_db', $holidayCheckQuery, [':date' => $date]);
        $dateIsConfirmedHoliday = !empty($holidayCheckResult['data']);
        $isHoliday = ($isHoliday || $dateIsConfirmedHoliday) ? 1 : 0;
        
        error_log("📅 Holiday Check - Date: $date, IsConfirmedHoliday: " . ($dateIsConfirmedHoliday ? 'YES' : 'NO') . ", FinalIsHoliday: $isHoliday");
        
        // ===== PROCESS EACH OVERTIME RECORD =====
        foreach ($overtimeData as $index => $record) {
            // ===== VALIDATE GID =====
            if (!isset($record['gid']) || empty($record['gid'])) {
                error_log("⚠️ Record $index: Skipping - missing GID");
                continue;
            }
            
            $gid = $record['gid'];
            $recordDate = $record['date'] ?? $date;
            
            // ===== GET OVERTIME HOURS =====
            $overtimeHours = isset($record['overtime_hours']) ? $record['overtime_hours'] : null;
            
            // ===== GET EMPLOYEE NAME =====
            $employeeName = '';
            $possibleNameFields = ['employee_name', 'name', 'emp_name', 'empName', 'employee', 'fullname', 'full_name'];
            
            foreach ($possibleNameFields as $field) {
                if (isset($record[$field]) && !empty($record[$field])) {
                    $employeeName = $record[$field];
                    break;
                }
            }
            
            // ===== FETCH EMPLOYEE NAME FROM DB IF NOT PROVIDED =====
            if (empty($employeeName)) {
                $empQuery = "SELECT name FROM employee_registration WHERE gid = :gid LIMIT 1";
                $empResult = DbManager::fetchPDOQueryData('spectra_db', $empQuery, [':gid' => $gid]);
                
                if (!empty($empResult['data']) && !empty($empResult['data'][0]['name'])) {
                    $employeeName = $empResult['data'][0]['name'];
                } else {
                    $employeeName = "Employee #$gid";
                    $missingNameCount++;
                    error_log("⚠️ GID $gid: Employee name not found in DB, using placeholder");
                }
            }
            
            // ===== DETERMINE IF THIS RECORD IS FOR A HOLIDAY =====
            $recordIsHoliday = isset($record['is_holiday']) ? (int)$record['is_holiday'] : $isHoliday;
            
            // ===== VALIDATE OVERTIME HOURS =====
            $wasEmptyOrInvalid = false;
            
            if ($overtimeHours === null || $overtimeHours === '' || !is_numeric($overtimeHours)) {
                $overtimeHours = 0;
                $wasEmptyOrInvalid = true;
                $invalidCount++;
                $zeroEntryCount++;
                error_log("⚠️ GID $gid, Date $recordDate: Invalid overtime hours, set to 0");
            } else {
                $overtimeHours = floatval($overtimeHours);
                if ($overtimeHours == 0) {
                    $zeroEntryCount++;
                }
            }
            
            // ===== CHECK IF ATTENDANCE RECORD EXISTS =====
            $checkQuery = "SELECT id, attendance_status, ot_status, status FROM employee_attendance 
                         WHERE gid = :gid AND attendance_date = :date LIMIT 1";
            
            $checkResult = DbManager::fetchPDOQueryData('spectra_db', $checkQuery, [
                ':gid' => $gid,
                ':date' => $recordDate
            ]);
            
            if (!empty($checkResult['data'])) {
                // ===== RECORD EXISTS - UPDATE IT =====
                $attendanceId = $checkResult['data'][0]['id'];
                $attendanceStatus = $checkResult['data'][0]['attendance_status'];
                $currentOtStatus = $checkResult['data'][0]['ot_status'] ?? null;
                $attendanceSubmissionStatus = $checkResult['data'][0]['status'] ?? null;
                
                error_log("🔄 GID $gid, Date $recordDate: Updating existing record");
                error_log("   ID: $attendanceId");
                error_log("   Current OT Status: $currentOtStatus");
                error_log("   Attendance Status: $attendanceStatus");
                error_log("   Attendance Submission Status: $attendanceSubmissionStatus");
                
                try {
                    // ===== CASE 1: EMPLOYEE ON LEAVE =====
                    if ($attendanceStatus == 'leave') {
                        error_log("📋 Case 1: EMPLOYEE ON LEAVE");
                        
                        $updateQuery = "UPDATE employee_attendance 
                                      SET overtime_hours = 0,
                                          ot_status = :ot_status,
                                          updated_at = NOW()
                                      WHERE id = :id";
                        
                        $updateParams = [
                            ':ot_status' => $newOtStatus,
                            ':id' => $attendanceId
                        ];
                        
                        $result = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);
                        
                        if ($result !== false) {
                            $leaveCount++;
                            error_log("✅ GID $gid, Date $recordDate: Updated leave OT");
                            error_log("   Set overtime_hours = 0");
                            error_log("   Set ot_status = $newOtStatus");
                        } else {
                            error_log("❌ GID $gid, Date $recordDate: Failed to update leave OT");
                        }
                    }
                    // ===== CASE 2: HOLIDAY RECORD =====
                    else if ($recordIsHoliday) {
                        error_log("📋 Case 2: HOLIDAY RECORD");
                        
                        // ✅ FIX: Allow submission for supervisors and admins
                        if (!$isSave && !$isAdmin && !$isSupervisor) {
                            // ❌ REGULAR USER cannot submit holiday overtime
                            error_log("❌ GID $gid, Date $recordDate: BLOCKED - Regular user cannot submit holiday overtime");
                            $updateQuery = "UPDATE employee_attendance 
                                          SET employee_name = :employee_name,
                                              overtime_hours = :overtime_hours,
                                              attendance_status = 'holiday',
                                              actual_man_hours = 0,
                                              ot_status = 'save',
                                              updated_at = NOW()
                                          WHERE id = :id";
                            
                            $updateParams = [
                                ':employee_name' => $employeeName,
                                ':overtime_hours' => $overtimeHours,
                                ':id' => $attendanceId
                            ];
                            
                            error_log("   ⚠️ Forcing ot_status to 'save' (not 'sub')");
                        } else {
                            // ✅ ADMIN or SUPERVISOR can submit holiday overtime
                            error_log("✅ GID $gid, Date $recordDate: ALLOWED - " . ($isAdmin ? 'Admin' : 'Supervisor') . " can submit holiday overtime");
                            $updateQuery = "UPDATE employee_attendance 
                                          SET employee_name = :employee_name,
                                              overtime_hours = :overtime_hours,
                                              attendance_status = 'holiday',
                                              actual_man_hours = 0,
                                              ot_status = :ot_status,
                                              updated_at = NOW()
                                          WHERE id = :id";
                            
                            $updateParams = [
                                ':employee_name' => $employeeName,
                                ':overtime_hours' => $overtimeHours,
                                ':ot_status' => $newOtStatus,
                                ':id' => $attendanceId
                            ];
                        }
                        
                        $result = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);
                        
                        if ($result !== false) {
                            $holidayCount++;
                            error_log("✅ GID $gid, Date $recordDate: Updated holiday OT");
                            error_log("   Set overtime_hours = $overtimeHours");
                            error_log("   Set ot_status = " . ($newOtStatus === 'save' || (!$isAdmin && !$isSupervisor) ? 'save' : $newOtStatus));
                            error_log("   Set attendance_status = 'holiday'");
                        } else {
                            error_log("❌ GID $gid, Date $recordDate: Failed to update holiday OT");
                        }
                    }
                    // ===== CASE 3: REGULAR OVERTIME =====
                    else {
                        error_log("📋 Case 3: REGULAR OVERTIME");
                        error_log("   ✅ Using OT_STATUS field (NOT attendance status)");
                        
                        // ✅ FIX: Check ot_status (OVERTIME status), NOT status (ATTENDANCE status)
                        $updateQuery = "UPDATE employee_attendance 
                                      SET overtime_hours = :overtime_hours,
                                          ot_status = :ot_status,
                                          updated_at = NOW()
                                      WHERE id = :id";
                        
                        $updateParams = [
                            ':overtime_hours' => $overtimeHours,
                            ':ot_status' => $newOtStatus,
                            ':id' => $attendanceId
                        ];
                        
                        $result = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);
                        
                        if ($result !== false) {
                            $updatedCount++;
                            error_log("✅ GID $gid, Date $recordDate: Updated regular OT");
                            error_log("   Set overtime_hours = $overtimeHours");
                            error_log("   Set ot_status = $newOtStatus");
                            error_log("   ✅ INDEPENDENT from attendance status");
                        } else {
                            error_log("❌ GID $gid, Date $recordDate: Failed to update regular OT");
                        }
                    }
                } catch (Exception $e) {
                    error_log("⚠️ GID $gid, Date $recordDate: Error updating OT record - " . $e->getMessage());
                    
                    // ===== FALLBACK: Try without employee_name =====
                    if (strpos($e->getMessage(), "employee_name") !== false) {
                        error_log("🔄 GID $gid, Date $recordDate: Retrying without employee_name field");
                        
                        $updateQuery = "UPDATE employee_attendance 
                                      SET overtime_hours = :overtime_hours,
                                          ot_status = :ot_status,
                                          updated_at = NOW()
                                      WHERE id = :id";
                        
                        $updateParams = [
                            ':overtime_hours' => $overtimeHours,
                            ':ot_status' => $newOtStatus,
                            ':id' => $attendanceId
                        ];
                        
                        try {
                            $result = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);
                            if ($result !== false) {
                                $updatedCount++;
                                error_log("✅ GID $gid, Date $recordDate: Updated OT (fallback, no employee_name)");
                            }
                        } catch (Exception $fallbackError) {
                            error_log("❌ GID $gid, Date $recordDate: Fallback also failed - " . $fallbackError->getMessage());
                            throw $fallbackError;
                        }
                    } else {
                        throw $e;
                    }
                }
            } else {
                // ===== RECORD DOESN'T EXIST - INSERT IT =====
                error_log("➕ GID $gid, Date $recordDate: Inserting new record");
                error_log("   Overtime Hours: $overtimeHours");
                error_log("   OT Status: $newOtStatus");
                
                try {
                    // ===== CASE 1: HOLIDAY RECORD =====
                    if ($recordIsHoliday) {
                        error_log("📋 Case 1: INSERTING HOLIDAY RECORD");
                        
                        // ✅ FIX: Allow submission for supervisors and admins
                        if (!$isSave && !$isAdmin && !$isSupervisor) {
                            // ❌ REGULAR USER cannot submit holiday overtime
                            error_log("❌ GID $gid, Date $recordDate: BLOCKED - Regular user cannot submit holiday overtime");
                            $insertQuery = "INSERT INTO employee_attendance 
                                         (employee_name, gid, attendance_date, overtime_hours, 
                                          attendance_status, actual_man_hours, ot_status, created_at)
                                         VALUES (:employee_name, :gid, :date, :overtime_hours, 
                                                 'holiday', 0, 'save', NOW())";
                            
                            $insertParams = [
                                ':employee_name' => $employeeName,
                                ':gid' => $gid,
                                ':date' => $recordDate,
                                ':overtime_hours' => $overtimeHours
                            ];
                            
                            error_log("   ⚠️ Forcing ot_status to 'save' (not 'sub')");
                        } else {
                            // ✅ ADMIN or SUPERVISOR can submit holiday overtime
                            error_log("✅ GID $gid, Date $recordDate: ALLOWED - " . ($isAdmin ? 'Admin' : 'Supervisor') . " can submit holiday overtime");
                            $insertQuery = "INSERT INTO employee_attendance 
                                         (employee_name, gid, attendance_date, overtime_hours, 
                                          attendance_status, actual_man_hours, ot_status, created_at)
                                         VALUES (:employee_name, :gid, :date, :overtime_hours, 
                                                 'holiday', 0, :ot_status, NOW())";
                            
                            $insertParams = [
                                ':employee_name' => $employeeName,
                                ':gid' => $gid,
                                ':date' => $recordDate,
                                ':overtime_hours' => $overtimeHours,
                                ':ot_status' => $newOtStatus
                            ];
                        }
                    }
                    // ===== CASE 2: REGULAR RECORD =====
                    else {
                        error_log("📋 Case 2: INSERTING REGULAR RECORD");
                        
                        $insertQuery = "INSERT INTO employee_attendance 
                                     (employee_name, gid, attendance_date, overtime_hours, ot_status, created_at)
                                     VALUES (:employee_name, :gid, :date, :overtime_hours, :ot_status, NOW())";
                        
                        $insertParams = [
                            ':employee_name' => $employeeName,
                            ':gid' => $gid,
                            ':date' => $recordDate,
                            ':overtime_hours' => $overtimeHours,
                            ':ot_status' => $newOtStatus
                        ];
                    }
                    
                    $result = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);
                    
                    if ($result !== false) {
                        if ($recordIsHoliday) {
                            $holidayCount++;
                            error_log("✅ GID $gid, Date $recordDate: Inserted holiday OT");
                            error_log("   Overtime Hours: $overtimeHours");
                            error_log("   OT Status: " . ($newOtStatus === 'save' || (!$isAdmin && !$isSupervisor) ? 'save' : $newOtStatus));
                        } else {
                            $updatedCount++;
                            error_log("✅ GID $gid, Date $recordDate: Inserted regular OT");
                            error_log("   Overtime Hours: $overtimeHours");
                            error_log("   OT Status: $newOtStatus");
                        }
                    } else {
                        error_log("❌ GID $gid, Date $recordDate: Failed to insert OT record");
                    }
                } catch (Exception $e) {
                    error_log("⚠️ GID $gid, Date $recordDate: Error inserting OT record - " . $e->getMessage());
                    
                    // ===== FALLBACK: Try without employee_name =====
                    if (strpos($e->getMessage(), "employee_name") !== false) {
                        error_log("🔄 GID $gid, Date $recordDate: Retrying insert without employee_name field");
                        
                        $insertQuery = "INSERT INTO employee_attendance 
                                     (gid, attendance_date, overtime_hours, ot_status, created_at)
                                     VALUES (:gid, :date, :overtime_hours, :ot_status, NOW())";
                        
                        $insertParams = [
                            ':gid' => $gid,
                            ':date' => $recordDate,
                            ':overtime_hours' => $overtimeHours,
                            ':ot_status' => $newOtStatus
                        ];
                        
                        try {
                            $result = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);
                            if ($result !== false) {
                                $updatedCount++;
                                error_log("✅ GID $gid, Date $recordDate: Inserted OT (fallback, no employee_name)");
                            }
                        } catch (Exception $fallbackError) {
                            error_log("❌ GID $gid, Date $recordDate: Fallback insert also failed - " . $fallbackError->getMessage());
                            throw $fallbackError;
                        }
                    } else {
                        throw $e;
                    }
                }
            }
        }
        
        // ===== BUILD RESPONSE MESSAGE =====
        $actionType = $isSave ? 'saved' : 'submitted';
        $message = "Overtime processed: ";
        $messageParts = [];
        
        if ($updatedCount > 0) {
            $messageParts[] = "$updatedCount regular record(s) $actionType";
        }
        if ($holidayCount > 0) {
            $messageParts[] = "$holidayCount holiday overtime record(s)";
        }
        if ($leaveCount > 0) {
            $messageParts[] = "$leaveCount on leave (OT set to 0)";
        }
        
        if (!empty($messageParts)) {
            $message .= implode(", ", $messageParts);
        } else {
            $message = "Overtime data processed successfully";
        }
        
        if ($zeroEntryCount > 0) {
            $message .= " | $zeroEntryCount zero/empty entries";
        }
        if ($invalidCount > 0) {
            $message .= " | $invalidCount invalid values (set to 0)";
        }
        if ($missingNameCount > 0) {
            $message .= " | $missingNameCount missing employee names";
        }
        
        error_log("📊 ===== OVERTIME SUBMISSION SUMMARY =====");
        error_log("   Updated: $updatedCount");
        error_log("   Holiday: $holidayCount");
        error_log("   Leave: $leaveCount");
        error_log("   Zero Entries: $zeroEntryCount");
        error_log("   Invalid: $invalidCount");
        error_log("   Missing Names: $missingNameCount");
        error_log("   Action: $actionType");
        error_log("   OT Status: $newOtStatus");
        error_log("   User Role: " . ($isAdmin ? 'ADMIN' : ($isSupervisor ? 'SUPERVISOR' : 'REGULAR')));
        error_log("   ✅ USING OT_STATUS FIELD: YES");
        error_log("   ✅ INDEPENDENT from attendance: YES");
        error_log("   ✅ TABLES DECOUPLED: YES");
        error_log("   ✅ HOLIDAY SUBMISSION: " . ($isAdmin || $isSupervisor ? 'ALLOWED' : 'BLOCKED'));
        error_log("=========================================");
        
        // ===== SEND SUCCESS RESPONSE =====
        echo json_encode([
            'success' => true,
            'message' => $message,
            'updated' => $updatedCount,
            'holiday' => $holidayCount,
            'leave' => $leaveCount,
            'zeroEntries' => $zeroEntryCount,
            'invalid' => $invalidCount,
            'missingNames' => $missingNameCount,
            'ot_status' => $newOtStatus,
            'action_type' => $actionType,
            'total_processed' => $updatedCount + $holidayCount + $leaveCount,
            'overtime_independent' => true,
            'tables_decoupled' => true,
            'user_role' => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'regular'),
            'holiday_submission_allowed' => ($isAdmin || $isSupervisor)
        ]);
        
    } catch (Exception $e) {
        error_log("❌ submitOvertimeForDate EXCEPTION: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString()
        ]);
    }
}

public function fetchAttendanceData() {
    try {
        // ========== STEP 1: AUTHENTICATION & AUTHORIZATION ==========
        $userInfo = SharedManager::getUser();
        $current_user_id = $userInfo['GID'] ?? null;
        $userModules = $userInfo['Modules'] ?? [];
        
        if (empty($current_user_id)) {
            throw new Exception('User authentication failed. Please login again.');
        }
        
        // ========== STEP 2: ROLE VALIDATION ==========
        $isAdmin = in_array(20, $userModules);
        $isSupervisor = in_array(21, $userModules);
        $isRegularUser = in_array(19, $userModules);
        
        error_log("✅ fetchAttendanceData - User: $current_user_id | Admin: " . ($isAdmin ? 'YES' : 'NO') . " | Supervisor: " . ($isSupervisor ? 'YES' : 'NO') . " | Regular: " . ($isRegularUser ? 'YES' : 'NO'));
        
        if (!$isAdmin && !$isSupervisor && !$isRegularUser) {
            error_log("❌ UNAUTHORIZED: User $current_user_id has no valid modules");
            throw new Exception('Access Denied: You do not have permission to access this feature');
        }
        
        // ========== STEP 3: PARSE REQUEST PARAMETERS ==========
        $sub_department = $_POST['sub_department'] ?? '';
        $group_type = $_POST['group_type'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $employment_type = $_POST['employment_type'] ?? '';
        $joined = $_POST['joined'] ?? '';
        $supervisor_id = $_POST['supervisor_id'] ?? null;
        $status_filter = $_POST['status_filter'] ?? 'all';
        
        // ===== NEW: SEARCH PARAMETERS =====
        $search_value = $_POST['search_value'] ?? '';
        $search_fields = isset($_POST['search_fields']) ? json_decode($_POST['search_fields'], true) : ['name', 'gid'];
        
        if (!is_array($search_fields)) {
            $search_fields = ['name', 'gid'];
        }
        
        $search_fields = array_intersect($search_fields, ['name', 'gid']); // Whitelist allowed fields
        
        error_log("🔍 Search Parameters: search_value='$search_value' | search_fields=" . implode(',', $search_fields));
        
        $transfer_check_dates = $_POST['transfer_check_dates'] ?? '[]';
        $transfer_check_start_date = $_POST['transfer_check_start_date'] ?? $start_date;
        $transfer_check_end_date = $_POST['transfer_check_end_date'] ?? $end_date;
        
        $transferCheckDates = [];
        try {
            $decoded = json_decode($transfer_check_dates, true);
            if (is_array($decoded) && !empty($decoded)) {
                $transferCheckDates = array_filter(array_map('trim', $decoded));
            }
        } catch (Exception $e) {
            error_log("⚠️ Transfer check dates parsing failed: " . $e->getMessage());
            $transferCheckDates = [];
        }
        
        error_log("📋 Request Parameters: dept=$sub_department | group=$group_type | dates=$start_date to $end_date | emp_type=$employment_type | joined=$joined | status=$status_filter");
        
        // ========== STEP 4: PAGINATION PARAMETERS ==========
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
        
        $allowed_per_page = [10, 20, 50, 100, 200, 500];
        if (!in_array($per_page, $allowed_per_page)) {
            $per_page = 10;
        }
        
        $offset = ($page - 1) * $per_page;
        
        error_log("📄 Pagination: Page=$page | PerPage=$per_page | Offset=$offset");
        
        // ========== STEP 5: VALIDATE DATE RANGE ==========
        if (empty($start_date) || empty($end_date)) {
            throw new Exception('Date range is required. Please select a date range.');
        }
        
        $start_check = DateTime::createFromFormat('Y-m-d', $start_date);
        $end_check = DateTime::createFromFormat('Y-m-d', $end_date);
        
        if (!$start_check || !$end_check || $start_check->format('Y-m-d') !== $start_date || $end_check->format('Y-m-d') !== $end_date) {
            throw new Exception('Invalid date format. Expected YYYY-MM-DD.');
        }
        
        error_log("📅 Date Range: $start_date to $end_date");
        
        // ========== STEP 6: PARSE JSON ARRAYS ==========
        $selected_departments = [];
        $selected_employment_types = [];
        
        if (!empty($sub_department)) {
            try {
                $decoded = json_decode($sub_department, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $selected_departments = array_filter(array_map('trim', $decoded));
                } else {
                    $selected_departments = [trim($sub_department)];
                }
                error_log("✅ Parsed Departments: " . implode(', ', $selected_departments));
            } catch (Exception $e) {
                $selected_departments = [trim($sub_department)];
                error_log("⚠️ Department parsing fallback: " . $selected_departments[0]);
            }
        }
        
        if (!empty($employment_type)) {
            try {
                $decoded = json_decode($employment_type, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $selected_employment_types = array_filter(array_map('trim', $decoded));
                } else {
                    $selected_employment_types = [trim($employment_type)];
                }
                error_log("✅ Parsed Employment Types: " . implode(', ', $selected_employment_types));
            } catch (Exception $e) {
                $selected_employment_types = [trim($employment_type)];
                error_log("⚠️ Employment type parsing fallback: " . $selected_employment_types[0]);
            }
        }
        
        // ========== STEP 7: BUILD FILTER CONDITIONS WITH STATUS = 'A' ==========
        $filter_conditions = "";
        $filter_params = [];
        
        // ===== CRITICAL: ADD STATUS FILTER FOR ACTIVE EMPLOYEES ONLY =====
        $filter_conditions .= " AND er.status = :employee_status";
        $filter_params[':employee_status'] = 'A';
        error_log("✅ Status Filter Applied: Only ACTIVE (A) employees will be retrieved");
        
        // ===== NEW: ADD SEARCH FILTER =====
        if (!empty($search_value)) {
            $search_conditions = [];
            $search_term = '%' . $search_value . '%';
            
            if (in_array('name', $search_fields)) {
                $search_conditions[] = "er.name LIKE :search_name";
                $filter_params[':search_name'] = $search_term;
            }
            
            if (in_array('gid', $search_fields)) {
                $search_conditions[] = "er.gid LIKE :search_gid";
                $filter_params[':search_gid'] = $search_term;
            }
            
            if (!empty($search_conditions)) {
                $filter_conditions .= " AND (" . implode(" OR ", $search_conditions) . ")";
                error_log("✅ Search Filter Applied: search_value='$search_value' | fields=" . implode(',', $search_fields));
            }
        }
        
        if ($isRegularUser && !$isAdmin && !$isSupervisor) {
            error_log("🔒 MODULE 19: Filtering for user $current_user_id only");
            $filter_conditions .= " AND er.gid = :module19_gid";
            $filter_params[':module19_gid'] = $current_user_id;
        }
        else if ($isAdmin || $isSupervisor) {
            
            if (!empty($joined) && ($joined === 'after' || $joined === 'before')) {
                $filter_conditions .= " AND er.joined = :joined_filter";
                $filter_params[':joined_filter'] = $joined;
                error_log("✅ Joined Filter Applied: er.joined = '$joined'");
            }
            
            if (!empty($selected_departments)) {
                $dept_conditions = [];
                $dept_params_to_add = [];
                
                foreach ($selected_departments as $index => $dept) {
                    $perm_param = ":sub_department_perm_" . $index;
                    $dept_conditions[] = "er.sub_department = $perm_param";
                    $dept_params_to_add[$perm_param] = $dept;
                    
                    if (!empty($transferCheckDates)) {
                        $temp_date_conditions = [];
                        
                        foreach ($transferCheckDates as $date_index => $check_date) {
                            $temp_param = ":sub_department_temp_" . $index . "_" . $date_index;
                            $date_param = ":date_check_" . $index . "_" . $date_index;
                            
                            $temp_date_conditions[] = "(
                                er.temp_sub_department = $temp_param 
                                AND er.transfer_from_date IS NOT NULL 
                                AND er.transfer_to_date IS NOT NULL 
                                AND $date_param BETWEEN er.transfer_from_date AND er.transfer_to_date
                            )";
                            
                            $dept_params_to_add[$temp_param] = $dept;
                            $dept_params_to_add[$date_param] = $check_date;
                        }
                        
                        if (!empty($temp_date_conditions)) {
                            $dept_conditions[] = "(" . implode(" OR ", $temp_date_conditions) . ")";
                        }
                    } else {
                        $temp_param = ":sub_department_temp_" . $index;
                        $date_check_param = ":date_check_" . $index;
                        
                        $dept_conditions[] = "(
                            er.temp_sub_department = $temp_param 
                            AND er.transfer_from_date IS NOT NULL 
                            AND er.transfer_to_date IS NOT NULL 
                            AND $date_check_param BETWEEN er.transfer_from_date AND er.transfer_to_date
                        )";
                        
                        $dept_params_to_add[$temp_param] = $dept;
                        $dept_params_to_add[$date_check_param] = $start_date;
                    }
                }
                
                $dept_filter = "(" . implode(" OR ", $dept_conditions) . ")";
                $filter_conditions .= " AND $dept_filter";
                $filter_params = array_merge($filter_params, $dept_params_to_add);
                
                error_log("✅ Final Department Filter: " . implode(', ', $selected_departments));
            }
            
            if (!empty($group_type) && $group_type !== 'Both') {
                $filter_conditions .= " AND (
                    er.group_type = :group_type_filter
                    OR 
                    er.temp_group_type = :group_type_filter_temp
                )";
                
                $filter_params[':group_type_filter'] = $group_type;
                $filter_params[':group_type_filter_temp'] = $group_type;
                error_log("✅ Group Type Filter: $group_type");
            }
            
            if (!empty($selected_employment_types)) {
                $emp_placeholders = [];
                
                foreach ($selected_employment_types as $index => $emp) {
                    $emp_placeholders[] = ":employment_type_" . $index;
                    $filter_params[":employment_type_" . $index] = $emp;
                }
                
                $emp_condition = "(" . implode(" OR ", 
                    array_map(function($p) { return "er.employment_type = $p"; }, $emp_placeholders)
                ) . ")";
                
                $filter_conditions .= " AND $emp_condition";
                error_log("✅ Employment Type Filter: " . implode(', ', $selected_employment_types));
            }
            
            if (!empty($supervisor_id) && $isSupervisor) {
                $filter_conditions .= " AND CONCAT(',', TRIM(REPLACE(er.supervisor, ' ', '')), ',') LIKE :supervisor_id";
                $filter_params[':supervisor_id'] = '%,' . trim($supervisor_id) . ',%';
                error_log("✅ Supervisor Filter: $supervisor_id");
            }
        }
        
        // ========== STEP 8: COUNT TOTAL EMPLOYEES ==========
        $count_query = "SELECT COUNT(*) as total_count
               FROM (
                   SELECT er.gid
                   FROM employee_registration er
                   WHERE 1=1 
                   " . $filter_conditions . "
                   GROUP BY er.gid
               ) as counted_employees";

        $count_params = $filter_params;

        error_log("🔍 Count Query executed");

        $count_result = DbManager::fetchPDOQueryData('spectra_db', $count_query, $count_params);
        
        if ($count_result === false) {
            error_log("❌ Count query failed");
            throw new Exception('Database error: Failed to count records');
        }
        
        $total_count = isset($count_result['data'][0]['total_count']) ? intval($count_result['data'][0]['total_count']) : 0;
        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 1;
        
        error_log("📊 Count Result: Total=$total_count | Pages=$total_pages");
        
        if ($page > $total_pages && $total_pages > 0) {
            error_log("⚠️ Page $page exceeds max $total_pages, resetting to 1");
            $page = 1;
            $offset = 0;
        }
        
        $display_start = ($total_count == 0) ? 0 : ($offset + 1);
        $display_end = min($offset + $per_page, $total_count);
        $pagination_text = "Showing $display_start to $display_end of $total_count entries";
        
        error_log("📊 Pagination: $pagination_text");
        
        // ========== STEP 9: FETCH EMPLOYEES FOR THIS PAGE ==========
        $employee_query = "SELECT 
                            er.gid,
                            er.name,
                            er.shift_type,
                            er.employment_type,
                            er.joined,
                            er.sub_department,
                            er.group_type,
                            er.temp_sub_department,
                            er.temp_group_type,
                            er.transfer_from_date,
                            er.transfer_to_date,
                            er.supervisor,
                            er.status,
                            CASE 
                                WHEN er.temp_sub_department IS NOT NULL 
                                     AND er.transfer_from_date IS NOT NULL 
                                     AND er.transfer_to_date IS NOT NULL 
                                     AND :check_date BETWEEN er.transfer_from_date AND er.transfer_to_date
                                THEN 1 
                                ELSE 0 
                            END as is_in_transfer,
                            CASE 
                                WHEN er.temp_sub_department IS NOT NULL 
                                     AND er.transfer_from_date IS NOT NULL 
                                     AND er.transfer_to_date IS NOT NULL 
                                THEN er.temp_sub_department 
                                ELSE NULL 
                            END as active_department,
                            CASE 
                                WHEN er.temp_sub_department IS NOT NULL 
                                     AND er.transfer_from_date IS NOT NULL 
                                     AND er.transfer_to_date IS NOT NULL 
                                     AND :check_date BETWEEN er.transfer_from_date AND er.transfer_to_date
                                THEN er.temp_sub_department 
                                ELSE er.sub_department 
                            END as current_department
                          FROM employee_registration er
                          WHERE 1=1 
                          " . $filter_conditions . "
                          GROUP BY er.gid
                          ORDER BY er.name ASC 
                          LIMIT " . intval($per_page) . " 
                          OFFSET " . intval($offset);

        $employee_params = $filter_params;
        $employee_params[':check_date'] = $start_date;

        error_log("🔍 Fetching ACTIVE employees for page $page");

        $employee_result = DbManager::fetchPDOQueryData('spectra_db', $employee_query, $employee_params);
        
        if ($employee_result === false) {
            error_log("❌ Employee query failed");
            throw new Exception('Database error: Failed to fetch employees');
        }
        
        $employeeData = $employee_result['data'] ?? [];
        
        error_log("✅ Fetched " . count($employeeData) . " UNIQUE ACTIVE employees for page $page");
        
        if (empty($employeeData)) {
            error_log("⚠️ No active employees found for this page");
            
            echo json_encode([
                'success' => true,
                'employees' => [],
                'attendance' => [],
                'leaves' => [],
                'holidays' => [],
                'holidays_by_joined' => [],
                'transfer_history' => [],
                'supervisor_filled_dates' => [],
                'page_wise_status' => [],
                'is_module_19' => ($isRegularUser && !$isAdmin && !$isSupervisor),
                'user_role' => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'regular_user'),
                'pagination' => [
                    'current_page' => intval($page),
                    'per_page' => intval($per_page),
                    'total' => intval($total_count),
                    'filtered' => 0,
                    'total_pages' => intval($total_pages),
                    'has_next' => false,
                    'has_prev' => false,
                    'display_start' => 0,
                    'display_end' => 0,
                    'pagination_text' => 'No active employees found'
                ],
                'counts' => [
                    'total_employees' => 0,
                    'page_employees' => 0,
                    'total_attendance_records' => 0,
                    'total_leave_records' => 0,
                    'total_holidays' => 0,
                    'transfer_history_records' => 0,
                    'supervisor_filled_dates_count' => 0,
                    'saved_records' => 0,
                    'submitted_records' => 0
                ],
                'date_range' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ],
                'message' => 'No active employees found for the selected criteria',
                'search_applied' => !empty($search_value)
            ]);
            exit;
        }
        
        $employee_gids = array_column($employeeData, 'gid');
        error_log("👥 Processing " . count($employee_gids) . " UNIQUE ACTIVE employee GIDs");
        
        // ========== STEP 10: ADD TRANSFER INFO ==========
        foreach ($employeeData as &$employee) {
            $hasValidTransfer = !empty($employee['temp_sub_department']) 
                                && !empty($employee['transfer_from_date']) 
                                && !empty($employee['transfer_to_date']);
            
            $employee['has_temp_transfer'] = $hasValidTransfer;
            
            if ($hasValidTransfer) {
                try {
                    $transferFrom = new DateTime($employee['transfer_from_date']);
                    $transferTo = new DateTime($employee['transfer_to_date']);
                    
                    $transferDatesInRange = [];
                    foreach ($transferCheckDates ?: [$start_date] as $checkDateStr) {
                        try {
                            $checkDate = new DateTime($checkDateStr);
                            if ($checkDate >= $transferFrom && $checkDate <= $transferTo) {
                                $transferDatesInRange[] = $checkDateStr;
                            }
                        } catch (Exception $e) {
                            error_log("⚠️ Invalid date format in transfer check: $checkDateStr");
                        }
                    }
                    
                    $employee['is_in_transfer_on_start_date'] = !empty($transferDatesInRange);
                    $employee['transfer_dates_in_range'] = $transferDatesInRange;
                    $employee['transfer_dates_count'] = count($transferDatesInRange);
                } catch (Exception $e) {
                    error_log("⚠️ Error processing transfer dates for GID {$employee['gid']}: " . $e->getMessage());
                    $employee['is_in_transfer_on_start_date'] = false;
                    $employee['transfer_dates_in_range'] = [];
                    $employee['transfer_dates_count'] = 0;
                }
            } else {
                $employee['is_in_transfer_on_start_date'] = false;
                $employee['transfer_dates_in_range'] = [];
                $employee['transfer_dates_count'] = 0;
            }
        }
        
        unset($employee);
        
        error_log("✅ Transfer info added to " . count($employeeData) . " employees");
        
        // ========== STEP 11: FETCH SHIFT ALLOCATIONS (ACTIVE ONLY) ==========
        $employeeShifts = [];
        
        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":shift_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $shift_query = "SELECT 
                                gid,
                                shift_type,
                                shift_from,
                                shift_to
                            FROM employee_registration
                            WHERE gid IN (" . implode(',', $gidPlaceholders) . ")
                            AND shift_from <= CURDATE()
                            AND shift_to >= CURDATE()
                            AND status = :shift_status";
            
            $shiftParams = array_merge($gidParams, [':shift_status' => 'A']);
            $shiftData = DbManager::fetchPDOQueryData('spectra_db', $shift_query, $shiftParams)['data'] ?? [];
            
            foreach ($shiftData as $shiftRecord) {
                $employeeShifts[$shiftRecord['gid']] = [
                    'shift_type' => $shiftRecord['shift_type'],
                    'shift_from' => $shiftRecord['shift_from'],
                    'shift_to' => $shiftRecord['shift_to']
                ];
            }
            
            foreach ($employeeData as &$employee) {
                if (isset($employeeShifts[$employee['gid']])) {
                    $employee['allocated_shift'] = $employeeShifts[$employee['gid']]['shift_type'];
                    $employee['has_shift_allocation'] = true;
                } else {
                    $employee['allocated_shift'] = null;
                    $employee['has_shift_allocation'] = false;
                }
            }
            
            unset($employee);
        }
        
        // ========== STEP 12: FETCH LEAVE DATA (ACTIVE EMPLOYEES ONLY) ==========
        $allLeaveData = [];
        
        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":leave_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $leave_query = "SELECT 
                                gid,
                                leave_type,
                                start_date,
                                end_date,
                                absence_detail,
                                created_at
                            FROM tbl_leave_management
                            WHERE gid IN (" . implode(',', $gidPlaceholders) . ")
                            AND (
                                (start_date BETWEEN :leave_start_date AND :leave_end_date)
                                OR (end_date BETWEEN :leave_start_date AND :leave_end_date)
                                OR (start_date <= :leave_start_date AND end_date >= :leave_end_date)
                            )
                            ORDER BY gid, start_date ASC";
            
            $leaveParams = array_merge($gidParams, [
                ':leave_start_date' => $start_date,
                ':leave_end_date' => $end_date
            ]);
            
            $allLeaveData = DbManager::fetchPDOQueryData('spectra_db', $leave_query, $leaveParams)['data'] ?? [];
        }
        
        // ========== STEP 13: FETCH HOLIDAYS WITH PROPER FORMATTING ==========
        $holiday_query = "SELECT date, day_type, joined 
                          FROM emp_yearly_holiday 
                          WHERE date BETWEEN :holiday_start_date AND :holiday_end_date
                          AND (day_type = 'holiday' OR day_type = 'half day')
                          ORDER BY date ASC";
        
        $holiday_params = [
            ':holiday_start_date' => $start_date,
            ':holiday_end_date' => $end_date
        ];
        
        $holidayData = DbManager::fetchPDOQueryData('spectra_db', $holiday_query, $holiday_params)['data'] ?? [];
        
        $holidays = [];
        $holidayDates = [];
        $holidaysByJoined = [];
        
        foreach ($holidayData as $holiday) {
            $dateStr = trim($holiday['date']);
            if (strpos($dateStr, '-') !== false) {
                $date_str = $dateStr;
            } else {
                $date_str = date('Y-m-d', strtotime($dateStr));
            }
            
            $day_type = $holiday['day_type'] ?? 'holiday';
            $joined_type = $holiday['joined'] ?? 'all';
            
            $holidays[$date_str] = $day_type;
            $holidayDates[] = $date_str;
            
            if (!isset($holidaysByJoined[$joined_type])) {
                $holidaysByJoined[$joined_type] = [];
            }
            $holidaysByJoined[$joined_type][] = [
                'date' => $date_str,
                'day_type' => $day_type
            ];
        }
        
        error_log("✅ Fetched " . count($holidays) . " holidays");
        
        // ========== STEP 14: MATCH EMPLOYEES WITH HOLIDAY ELIGIBILITY ==========
        $employeeHolidayEligibility = [];
        
        foreach ($employeeData as $employee) {
            $gid = $employee['gid'];
            $employee_joined = $employee['joined'] ?? '';
            
            $eligible_holidays = [];
            
            if (!empty($joined)) {
                if ($employee_joined === $joined) {
                    $eligible_holidays = $holidaysByJoined[$joined] ?? [];
                } else {
                    // Do nothing
                }
            } else {
                if (isset($holidaysByJoined[$employee_joined])) {
                    $eligible_holidays = $holidaysByJoined[$employee_joined];
                } elseif (isset($holidaysByJoined['all'])) {
                    $eligible_holidays = $holidaysByJoined['all'];
                }
            }
            
            $employeeHolidayEligibility[$gid] = [
                'joined' => $employee_joined,
                'eligible_holidays' => $eligible_holidays,
                'eligible_holiday_dates' => array_column($eligible_holidays, 'date')
            ];
        }
        
        error_log("✅ Holiday eligibility mapped for " . count($employeeData) . " employees");
        
        // ========== STEP 15: FETCH ATTENDANCE DATA (ACTIVE EMPLOYEES ONLY) ==========
        $attendanceData = [];
        $savedRecordsCount = 0;
        $submittedRecordsCount = 0;
        
        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":attendance_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $attendance_query = "SELECT 
                                    a.id,
                                    a.gid, 
                                    a.attendance_date, 
                                    a.attendance_status,
                                    a.actual_man_hours,
                                    a.status,
                                    a.ot_status,
                                    a.overtime_hours,
                                    a.created_at
                                FROM employee_attendance a
                                WHERE a.attendance_date BETWEEN :attendance_start_date AND :attendance_end_date
                                AND a.gid IN (" . implode(',', $gidPlaceholders) . ")
                                ORDER BY a.gid ASC, a.attendance_date ASC";
            
            $attendanceParams = array_merge($gidParams, [
                ':attendance_start_date' => $start_date,
                ':attendance_end_date' => $end_date
            ]);
            
            $attendanceData = DbManager::fetchPDOQueryData('spectra_db', $attendance_query, $attendanceParams)['data'] ?? [];
            
            foreach ($attendanceData as $record) {
                if ($record['status'] === 'save') {
                    $savedRecordsCount++;
                } elseif ($record['status'] === 'sub') {
                    $submittedRecordsCount++;
                }
            }
        }
        
        error_log("✅ Fetched " . count($attendanceData) . " attendance records");
        
        // ========== STEP 16: IDENTIFY SUPERVISOR-FILLED DATES (MODULE 19) ==========
        $supervisorFilledDates = [];
        if ($isRegularUser && !$isAdmin && !$isSupervisor) {
            foreach ($attendanceData as $record) {
                if (!empty($record['attendance_status'])) {
                    $supervisorFilledDates[] = $record['attendance_date'];
                }
            }
            error_log("🔒 Module 19: Supervisor-filled dates: " . count($supervisorFilledDates));
        }
        
        // ========== STEP 17: FETCH TRANSFER HISTORY (ACTIVE EMPLOYEES ONLY) ==========
        $allTransferHistory = [];
        
        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":transfer_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $transfer_query = "SELECT 
                                gid,
                                sub_department,
                                temp_sub_department,
                                transfer_from_date,
                                transfer_to_date
                            FROM employee_registration
                            WHERE gid IN (" . implode(',', $gidPlaceholders) . ")
                            AND temp_sub_department IS NOT NULL
                            AND temp_sub_department != ''
                            AND transfer_from_date IS NOT NULL
                            AND transfer_to_date IS NOT NULL
                            AND status = :transfer_status
                            ORDER BY gid ASC, transfer_to_date DESC";
            
            $transferParams = array_merge($gidParams, [':transfer_status' => 'A']);
            $allTransferHistory = DbManager::fetchPDOQueryData('spectra_db', $transfer_query, $transferParams)['data'] ?? [];
        }
        
        error_log("✅ Fetched " . count($allTransferHistory) . " transfer records");
        
        // ========== STEP 18: CALCULATE PAGE-WISE STATUS FOR EACH DATE ==========
        $pageWiseStatusByDate = [];
        $dateRange = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            new DateTime($end_date . ' +1 day')
        );

        foreach ($dateRange as $dateObj) {
            $dateStr = $dateObj->format('Y-m-d');
            $savedCountOnPage = 0;
            $submittedCountOnPage = 0;
            $unfilledCountOnPage = 0;
            $isHolidayDate = in_array($dateStr, $holidayDates);
            $holidayType = $isHolidayDate ? ($holidays[$dateStr] ?? 'holiday') : null;
            
            $employeeAttendanceMap = [];
            foreach ($attendanceData as $record) {
                if ($record['attendance_date'] === $dateStr) {
                    $employeeAttendanceMap[$record['gid']] = $record['status'];
                }
            }
            
            foreach ($employeeData as $employee) {
                $gid = $employee['gid'];
                
                if (isset($employeeAttendanceMap[$gid])) {
                    if ($employeeAttendanceMap[$gid] === 'save') {
                        $savedCountOnPage++;
                    } elseif ($employeeAttendanceMap[$gid] === 'sub') {
                        $submittedCountOnPage++;
                    }
                } else {
                    if (!$isHolidayDate) {
                        $unfilledCountOnPage++;
                    }
                }
            }
            
            $totalEmployeesForDate = $savedCountOnPage + $submittedCountOnPage + $unfilledCountOnPage;

            $pageStatus = null;
            if ($totalEmployeesForDate > 0) {
                if ($submittedCountOnPage === $totalEmployeesForDate) {
                    $pageStatus = 'sub';
                } elseif ($savedCountOnPage > 0) {
                    $pageStatus = 'save';
                } elseif ($unfilledCountOnPage > 0) {
                    $pageStatus = 'incomplete';
                }
            } elseif ($isHolidayDate) {
                $pageStatus = 'holiday';
            }
            
            $pageWiseStatusByDate[$dateStr] = [
                'status' => $pageStatus,
                'saved' => $savedCountOnPage,
                'submitted' => $submittedCountOnPage,
                'unfilled' => $unfilledCountOnPage,
                'total_employees_on_page_for_date' => $totalEmployeesForDate,
                'page' => $page,
                'total_pages' => $total_pages,
                'is_holiday' => $isHolidayDate,
                'holiday_type' => $holidayType,
                'joined_filter_applied' => !empty($joined) ? $joined : 'none'
            ];
        }

        error_log("✅ Page-wise status calculated for " . count($pageWiseStatusByDate) . " dates");
        
        // ========== STEP 19: RETURN SUCCESS RESPONSE ==========
        error_log("✅ RESPONSE: Returning " . count($employeeData) . " UNIQUE ACTIVE employees with attendance data");
        
        echo json_encode([
            'success' => true,
            'employees' => array_map(function($emp) use ($employeeHolidayEligibility) {
                $emp['holiday_eligibility'] = $employeeHolidayEligibility[$emp['gid']] ?? [
                    'joined' => null,
                    'eligible_holidays' => [],
                    'eligible_holiday_dates' => []
                ];
                return $emp;
            }, $employeeData),
            'attendance' => $attendanceData,
            'leaves' => $allLeaveData,
            'holidays' => $holidays,
            'holidays_by_joined' => $holidaysByJoined,
            'transfer_history' => $allTransferHistory,
            'supervisor_filled_dates' => $supervisorFilledDates,
            'is_module_19' => ($isRegularUser && !$isAdmin && !$isSupervisor),
            'user_role' => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'regular_user'),
            
            'pagination' => [
                'current_page' => intval($page),
                'per_page' => intval($per_page),
                'total' => intval($total_count),
                'filtered' => intval(count($employeeData)),
                'total_pages' => intval($total_pages),
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1,
                'display_start' => intval($display_start),
                'display_end' => intval($display_end),
                'pagination_text' => $pagination_text
            ],
            
            'counts' => [
                'total_employees' => intval($total_count),
                'page_employees' => intval(count($employeeData)),
                'total_attendance_records' => intval(count($attendanceData)),
                'total_leave_records' => intval(count($allLeaveData)),
                'total_holidays' => intval(count($holidays)),
                'transfer_history_records' => intval(count($allTransferHistory)),
                'supervisor_filled_dates_count' => intval(count($supervisorFilledDates)),
                'saved_records' => intval($savedRecordsCount),
                'submitted_records' => intval($submittedRecordsCount)
            ],
            
            'date_range' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ],
            
            'page_wise_status' => $pageWiseStatusByDate,
            
            'filters_applied' => [
                'employee_status' => 'ACTIVE (A)',
                'sub_departments' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($selected_departments) ? implode(', ', $selected_departments) : 'None'),
                'group_type' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($group_type) ? $group_type : 'None'),
                'employment_types' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($selected_employment_types) ? implode(', ', $selected_employment_types) : 'None'),
                'joined' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($joined) ? $joined : 'All'),
                'status_filter' => $status_filter,
                'search_value' => !empty($search_value) ? $search_value : 'None',
                'search_fields' => !empty($search_value) ? implode(', ', $search_fields) : 'N/A'
            ],
            
            'status_summary' => [
                'saved' => intval($savedRecordsCount),
                'submitted' => intval($submittedRecordsCount),
                'total' => intval(count($attendanceData))
            ],
            
            'holiday_eligibility_summary' => [
                'total_employees_with_holidays' => count(array_filter($employeeHolidayEligibility, function($e) {
                    return !empty($e['eligible_holiday_dates']);
                })),
                'total_employees_without_holidays' => count(array_filter($employeeHolidayEligibility, function($e) {
                    return empty($e['eligible_holiday_dates']);
                })),
                'joined_filter_applied' => !empty($joined) ? $joined : 'none'
            ],
            
            'search_applied' => !empty($search_value)
        ]);
        
    } catch (Exception $e) {
        error_log("❌ EXCEPTION in fetchAttendanceData: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => $e->getMessage(),
            'page_wise_status' => [],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'filtered' => 0,
                'total_pages' => 0,
                'has_next' => false,
                'has_prev' => false,
                'display_start' => 0,
                'display_end' => 0,
                'pagination_text' => 'Error loading data'
            ]
        ]);
    }
}

public function fetchOvertimeData() {
    try {
        // ========== STEP 1: AUTHENTICATION & AUTHORIZATION ==========
        $userInfo = SharedManager::getUser();
        $current_user_id = $userInfo['GID'] ?? null;
        $userModules = $userInfo['Modules'] ?? [];
        
        if (empty($current_user_id)) {
            throw new Exception('User authentication failed. Please login again.');
        }
        
        // ========== STEP 2: ROLE VALIDATION ==========
        $isAdmin = in_array(20, $userModules);
        $isSupervisor = in_array(21, $userModules);
        $isRegularUser = in_array(19, $userModules);
        
        error_log("✅ fetchOvertimeData - User: $current_user_id | Admin: " . ($isAdmin ? 'YES' : 'NO') . " | Supervisor: " . ($isSupervisor ? 'YES' : 'NO') . " | Regular: " . ($isRegularUser ? 'YES' : 'NO'));
        
        if (!$isAdmin && !$isSupervisor && !$isRegularUser) {
            error_log("❌ UNAUTHORIZED: User $current_user_id has no valid modules");
            throw new Exception('Access Denied: You do not have permission to access this feature');
        }
        
        // ========== STEP 3: PARSE REQUEST PARAMETERS ==========
        $sub_department = $_POST['sub_department'] ?? '';
        $group_type = $_POST['group_type'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $employment_type = $_POST['employment_type'] ?? '';
        $joined = $_POST['joined'] ?? '';
        $supervisor_id = $_POST['supervisor_id'] ?? null;
        $status_filter = $_POST['status_filter'] ?? 'all';
        
        // ===== NEW: SEARCH PARAMETERS =====
        $search_value = $_POST['search_value'] ?? '';
        $search_fields = isset($_POST['search_fields']) ? json_decode($_POST['search_fields'], true) : ['name', 'gid'];
        
        if (!is_array($search_fields)) {
            $search_fields = ['name', 'gid'];
        }
        
        $search_fields = array_intersect($search_fields, ['name', 'gid']); // Whitelist allowed fields
        
        error_log("🔍 Search Parameters: search_value='$search_value' | search_fields=" . implode(',', $search_fields));
        
        $transfer_check_dates = $_POST['transfer_check_dates'] ?? '[]';
        
        $transferCheckDates = [];
        try {
            $decoded = json_decode($transfer_check_dates, true);
            if (is_array($decoded) && !empty($decoded)) {
                $transferCheckDates = array_filter(array_map('trim', $decoded));
            }
        } catch (Exception $e) {
            error_log("⚠️ Transfer check dates parsing failed: " . $e->getMessage());
            $transferCheckDates = [];
        }
        
        error_log("📋 Request Parameters: dept=$sub_department | group=$group_type | dates=$start_date to $end_date | emp_type=$employment_type | joined=$joined | status=$status_filter");
        
        // ========== STEP 4: PAGINATION PARAMETERS ==========
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        
        $allowed_per_page = [10, 20, 50, 100, 200, 500];
        if (!in_array($per_page, $allowed_per_page)) {
            $per_page = 10;
        }
        
        $offset = ($page - 1) * $per_page;
        
        error_log("📄 Pagination: Page=$page | PerPage=$per_page | Offset=$offset");
        
        // ========== STEP 5: VALIDATE DATE RANGE ==========
        if (empty($start_date) || empty($end_date)) {
            throw new Exception('Date range is required. Please select a date range.');
        }
        
        $start_check = DateTime::createFromFormat('Y-m-d', $start_date);
        $end_check = DateTime::createFromFormat('Y-m-d', $end_date);
        
        if (!$start_check || !$end_check || $start_check->format('Y-m-d') !== $start_date || $end_check->format('Y-m-d') !== $end_date) {
            throw new Exception('Invalid date format. Expected YYYY-MM-DD.');
        }
        
        error_log("📅 Date Range: $start_date to $end_date");
        
        // ========== STEP 6: PARSE JSON ARRAYS ==========
        $selected_departments = [];
        $selected_employment_types = [];
        
        if (!empty($sub_department)) {
            try {
                $decoded = json_decode($sub_department, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $selected_departments = array_filter(array_map('trim', $decoded));
                } else {
                    $selected_departments = [trim($sub_department)];
                }
                error_log("✅ Parsed Departments: " . implode(', ', $selected_departments));
            } catch (Exception $e) {
                $selected_departments = [trim($sub_department)];
            }
        }
        
        if (!empty($employment_type)) {
            try {
                $decoded = json_decode($employment_type, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $selected_employment_types = array_filter(array_map('trim', $decoded));
                } else {
                    $selected_employment_types = [trim($employment_type)];
                }
                error_log("✅ Parsed Employment Types: " . implode(', ', $selected_employment_types));
            } catch (Exception $e) {
                $selected_employment_types = [trim($employment_type)];
            }
        }
        
        // ========== STEP 7: BUILD FILTER CONDITIONS WITH STATUS = 'A' ==========
        $filter_conditions = "";
        $filter_params = [];
        
        // ===== CRITICAL: ADD STATUS FILTER FOR ACTIVE EMPLOYEES ONLY =====
        $filter_conditions .= " AND er.status = :employee_status";
        $filter_params[':employee_status'] = 'A';
        error_log("✅ Status Filter Applied: Only ACTIVE (A) employees will be retrieved");
        
        // ===== NEW: ADD SEARCH FILTER =====
        if (!empty($search_value)) {
            $search_conditions = [];
            $search_term = '%' . $search_value . '%';
            
            if (in_array('name', $search_fields)) {
                $search_conditions[] = "er.name LIKE :search_name";
                $filter_params[':search_name'] = $search_term;
            }
            
            if (in_array('gid', $search_fields)) {
                $search_conditions[] = "er.gid LIKE :search_gid";
                $filter_params[':search_gid'] = $search_term;
            }
            
            if (!empty($search_conditions)) {
                $filter_conditions .= " AND (" . implode(" OR ", $search_conditions) . ")";
                error_log("✅ Search Filter Applied: search_value='$search_value' | fields=" . implode(',', $search_fields));
            }
        }
        
        if ($isRegularUser && !$isAdmin && !$isSupervisor) {
            error_log("🔒 MODULE 19: Filtering for user $current_user_id only");
            $filter_conditions .= " AND er.gid = :module19_gid";
            $filter_params[':module19_gid'] = $current_user_id;
        }
        else if ($isAdmin || $isSupervisor) {
            
            if (!empty($joined) && ($joined === 'after' || $joined === 'before')) {
                $filter_conditions .= " AND er.joined = :joined_filter";
                $filter_params[':joined_filter'] = $joined;
                error_log("✅ Joined Filter Applied: er.joined = '$joined'");
            }
            
            if (!empty($selected_departments)) {
                $dept_conditions = [];
                $dept_params_to_add = [];
                
                foreach ($selected_departments as $index => $dept) {
                    $perm_param = ":sub_department_perm_" . $index;
                    $dept_conditions[] = "er.sub_department = $perm_param";
                    $dept_params_to_add[$perm_param] = $dept;
                    
                    if (!empty($transferCheckDates)) {
                        $temp_date_conditions = [];
                        
                        foreach ($transferCheckDates as $date_index => $check_date) {
                            $temp_param = ":sub_department_temp_" . $index . "_" . $date_index;
                            $date_param = ":date_check_" . $index . "_" . $date_index;
                            
                            $temp_date_conditions[] = "(
                                er.temp_sub_department = $temp_param 
                                AND er.transfer_from_date IS NOT NULL 
                                AND er.transfer_to_date IS NOT NULL 
                                AND $date_param BETWEEN er.transfer_from_date AND er.transfer_to_date
                            )";
                            
                            $dept_params_to_add[$temp_param] = $dept;
                            $dept_params_to_add[$date_param] = $check_date;
                        }
                        
                        if (!empty($temp_date_conditions)) {
                            $dept_conditions[] = "(" . implode(" OR ", $temp_date_conditions) . ")";
                        }
                    } else {
                        $temp_param = ":sub_department_temp_" . $index;
                        $date_check_param = ":date_check_" . $index;
                        
                        $dept_conditions[] = "(
                            er.temp_sub_department = $temp_param 
                            AND er.transfer_from_date IS NOT NULL 
                            AND er.transfer_to_date IS NOT NULL 
                            AND $date_check_param BETWEEN er.transfer_from_date AND er.transfer_to_date
                        )";
                        
                        $dept_params_to_add[$temp_param] = $dept;
                        $dept_params_to_add[$date_check_param] = $start_date;
                    }
                }
                
                $dept_filter = "(" . implode(" OR ", $dept_conditions) . ")";
                $filter_conditions .= " AND $dept_filter";
                $filter_params = array_merge($filter_params, $dept_params_to_add);
            }
            
            if (!empty($group_type) && $group_type !== 'Both') {
                $filter_conditions .= " AND (
                    er.group_type = :group_type_filter
                    OR 
                    er.temp_group_type = :group_type_filter_temp
                )";
                
                $filter_params[':group_type_filter'] = $group_type;
                $filter_params[':group_type_filter_temp'] = $group_type;
                error_log("✅ Group Type Filter: $group_type");
            }
            
            if (!empty($selected_employment_types)) {
                $emp_placeholders = [];
                
                foreach ($selected_employment_types as $index => $emp) {
                    $emp_placeholders[] = ":employment_type_" . $index;
                    $filter_params[":employment_type_" . $index] = $emp;
                }
                
                $emp_condition = "(" . implode(" OR ", 
                    array_map(function($p) { return "er.employment_type = $p"; }, $emp_placeholders)
                ) . ")";
                
                $filter_conditions .= " AND $emp_condition";
                error_log("✅ Employment Type Filter: " . implode(', ', $selected_employment_types));
            }
            
            if (!empty($supervisor_id) && $isSupervisor) {
                $filter_conditions .= " AND CONCAT(',', TRIM(REPLACE(er.supervisor, ' ', '')), ',') LIKE :supervisor_id";
                $filter_params[':supervisor_id'] = '%,' . trim($supervisor_id) . ',%';
                error_log("✅ Supervisor Filter: $supervisor_id");
            }
        }
        
        // ========== STEP 8: COUNT TOTAL EMPLOYEES ==========
        $count_query = "SELECT COUNT(*) as total_count
               FROM (
                   SELECT er.gid
                   FROM employee_registration er
                   WHERE 1=1 
                   " . $filter_conditions . "
                   GROUP BY er.gid
               ) as counted_employees";

        $count_params = $filter_params;

        error_log("🔍 Count Query executed");

        $count_result = DbManager::fetchPDOQueryData('spectra_db', $count_query, $count_params);
        
        if ($count_result === false) {
            error_log("❌ Count query failed");
            throw new Exception('Database error: Failed to count records');
        }
        
        $total_count = isset($count_result['data'][0]['total_count']) ? intval($count_result['data'][0]['total_count']) : 0;
        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 1;
        
        error_log("📊 Count Result: Total=$total_count | Pages=$total_pages");
        
        if ($page > $total_pages && $total_pages > 0) {
            error_log("⚠️ Page $page exceeds max $total_pages, resetting to 1");
            $page = 1;
            $offset = 0;
        }
        
        $display_start = ($total_count == 0) ? 0 : ($offset + 1);
        $display_end = min($offset + $per_page, $total_count);
        $pagination_text = "Showing $display_start to $display_end of $total_count entries";
        
        error_log("📊 Pagination: $pagination_text");
        
        // ========== STEP 9: FETCH EMPLOYEES FOR THIS PAGE ==========
        $employee_query = "SELECT 
                            er.gid,
                            er.name,
                            er.shift_type,
                            er.employment_type,
                            er.joined,
                            er.sub_department,
                            er.group_type,
                            er.temp_sub_department,
                            er.temp_group_type,
                            er.transfer_from_date,
                            er.transfer_to_date,
                            er.supervisor,
                            er.status,
                            CASE 
                                WHEN er.temp_sub_department IS NOT NULL 
                                     AND er.transfer_from_date IS NOT NULL 
                                     AND er.transfer_to_date IS NOT NULL 
                                     AND :check_date BETWEEN er.transfer_from_date AND er.transfer_to_date
                                THEN 1 
                                ELSE 0 
                            END as is_in_transfer,
                            CASE 
                                WHEN er.temp_sub_department IS NOT NULL 
                                     AND er.transfer_from_date IS NOT NULL 
                                     AND er.transfer_to_date IS NOT NULL 
                                THEN er.temp_sub_department 
                                ELSE NULL 
                            END as active_department,
                            CASE 
                                WHEN er.temp_sub_department IS NOT NULL 
                                     AND er.transfer_from_date IS NOT NULL 
                                     AND er.transfer_to_date IS NOT NULL 
                                     AND :check_date BETWEEN er.transfer_from_date AND er.transfer_to_date
                                THEN er.temp_sub_department 
                                ELSE er.sub_department 
                            END as current_department
                          FROM employee_registration er
                          WHERE 1=1 
                          " . $filter_conditions . "
                          GROUP BY er.gid
                          ORDER BY er.name ASC 
                          LIMIT " . intval($per_page) . " 
                          OFFSET " . intval($offset);

        $employee_params = $filter_params;
        $employee_params[':check_date'] = $start_date;

        error_log("🔍 Fetching ACTIVE employees for page $page");

        $employee_result = DbManager::fetchPDOQueryData('spectra_db', $employee_query, $employee_params);
        
        if ($employee_result === false) {
            error_log("❌ Employee query failed");
            throw new Exception('Database error: Failed to fetch employees');
        }
        
        $employeeData = $employee_result['data'] ?? [];
        
        error_log("✅ Fetched " . count($employeeData) . " UNIQUE ACTIVE employees for page $page");
        
        if (empty($employeeData)) {
            error_log("⚠️ No active employees found for this page");
            
            echo json_encode([
                'success' => true,
                'employees' => [],
                'overtime' => [],
                'holiday_overtime' => [],
                'leaves' => [],
                'holidays' => [],
                'holidays_by_joined' => [],
                'transfer_history' => [],
                'supervisor_filled_dates' => [],
                'page_wise_status' => [],
                'is_module_19' => ($isRegularUser && !$isAdmin && !$isSupervisor),
                'user_role' => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'regular_user'),
                'pagination' => [
                    'current_page' => intval($page),
                    'per_page' => intval($per_page),
                    'total' => intval($total_count),
                    'filtered' => 0,
                    'total_pages' => intval($total_pages),
                    'has_next' => false,
                    'has_prev' => false,
                    'display_start' => 0,
                    'display_end' => 0,
                    'pagination_text' => 'No active employees found'
                ],
                'counts' => [
                    'total_employees' => 0,
                    'page_employees' => 0,
                    'total_overtime_records' => 0,
                    'total_holiday_overtime_records' => 0,
                    'total_leave_records' => 0,
                    'total_holidays' => 0,
                    'transfer_history_records' => 0,
                    'supervisor_filled_dates_count' => 0,
                    'saved_records' => 0,
                    'submitted_records' => 0,
                    'holiday_saved_records' => 0,
                    'holiday_submitted_records' => 0
                ],
                'date_range' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ],
                'message' => 'No active employees found for the selected criteria',
                'search_applied' => !empty($search_value)
            ]);
            exit;
        }
        
        $employee_gids = array_column($employeeData, 'gid');
        error_log("👥 Processing " . count($employee_gids) . " UNIQUE ACTIVE employee GIDs");

        // ========== STEP 10: ADD TRANSFER INFO ==========
        foreach ($employeeData as &$employee) {
            $hasValidTransfer = !empty($employee['temp_sub_department']) 
                                && !empty($employee['transfer_from_date']) 
                                && !empty($employee['transfer_to_date']);
            
            $employee['has_temp_transfer'] = $hasValidTransfer;
            
            if ($hasValidTransfer) {
                try {
                    $transferFrom = new DateTime($employee['transfer_from_date']);
                    $transferTo = new DateTime($employee['transfer_to_date']);
                    
                    $transferDatesInRange = [];
                    foreach ($transferCheckDates ?: [$start_date] as $checkDateStr) {
                        try {
                            $checkDate = new DateTime($checkDateStr);
                            if ($checkDate >= $transferFrom && $checkDate <= $transferTo) {
                                $transferDatesInRange[] = $checkDateStr;
                            }
                        } catch (Exception $e) {
                            error_log("⚠️ Invalid date format in transfer check: $checkDateStr");
                        }
                    }
                    
                    $employee['is_in_transfer_on_start_date'] = !empty($transferDatesInRange);
                    $employee['transfer_dates_in_range'] = $transferDatesInRange;
                    $employee['transfer_dates_count'] = count($transferDatesInRange);
                } catch (Exception $e) {
                    error_log("⚠️ Error processing transfer dates for GID {$employee['gid']}: " . $e->getMessage());
                    $employee['is_in_transfer_on_start_date'] = false;
                    $employee['transfer_dates_in_range'] = [];
                    $employee['transfer_dates_count'] = 0;
                }
            } else {
                $employee['is_in_transfer_on_start_date'] = false;
                $employee['transfer_dates_in_range'] = [];
                $employee['transfer_dates_count'] = 0;
            }
        }
        
        unset($employee);
        
        error_log("✅ Transfer info added to " . count($employeeData) . " employees");
        
        // ========== STEP 11: FETCH SHIFT ALLOCATIONS (ACTIVE ONLY) ==========
        $employeeShifts = [];
        
        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":shift_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $shift_query = "SELECT 
                                gid,
                                shift_type,
                                shift_from,
                                shift_to
                            FROM employee_registration
                            WHERE gid IN (" . implode(',', $gidPlaceholders) . ")
                            AND shift_from <= CURDATE()
                            AND shift_to >= CURDATE()
                            AND status = :shift_status";
            
            $shiftParams = array_merge($gidParams, [':shift_status' => 'A']);
            $shiftData = DbManager::fetchPDOQueryData('spectra_db', $shift_query, $shiftParams)['data'] ?? [];
            
            foreach ($shiftData as $shiftRecord) {
                $employeeShifts[$shiftRecord['gid']] = [
                    'shift_type' => $shiftRecord['shift_type'],
                    'shift_from' => $shiftRecord['shift_from'],
                    'shift_to' => $shiftRecord['shift_to']
                ];
            }
            
            foreach ($employeeData as &$employee) {
                if (isset($employeeShifts[$employee['gid']])) {
                    $employee['allocated_shift'] = $employeeShifts[$employee['gid']]['shift_type'];
                    $employee['has_shift_allocation'] = true;
                } else {
                    $employee['allocated_shift'] = null;
                    $employee['has_shift_allocation'] = false;
                }
            }
            
            unset($employee);
        }
        
        // ========== STEP 12: FETCH LEAVE DATA (ACTIVE EMPLOYEES ONLY) ==========
        $allLeaveData = [];
        
        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":leave_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $leave_query = "SELECT 
                                gid,
                                leave_type,
                                start_date,
                                end_date,
                                absence_detail,
                                created_at
                            FROM tbl_leave_management
                            WHERE gid IN (" . implode(',', $gidPlaceholders) . ")
                            AND (
                                (start_date BETWEEN :leave_start_date AND :leave_end_date)
                                OR (end_date BETWEEN :leave_start_date AND :leave_end_date)
                                OR (start_date <= :leave_start_date AND end_date >= :leave_end_date)
                            )
                            ORDER BY gid, start_date ASC";
            
            $leaveParams = array_merge($gidParams, [
                ':leave_start_date' => $start_date,
                ':leave_end_date' => $end_date
            ]);
            
            $allLeaveData = DbManager::fetchPDOQueryData('spectra_db', $leave_query, $leaveParams)['data'] ?? [];
        }
        
        error_log("✅ Fetched " . count($allLeaveData) . " leave records");
        
        // ========== STEP 13: FETCH HOLIDAYS WITH PROPER FORMATTING ==========
        $holiday_query = "SELECT date, day_type, joined 
                          FROM emp_yearly_holiday 
                          WHERE date BETWEEN :holiday_start_date AND :holiday_end_date
                          AND (day_type = 'holiday' OR day_type = 'half day')
                          ORDER BY date ASC";
        
        $holiday_params = [
            ':holiday_start_date' => $start_date,
            ':holiday_end_date' => $end_date
        ];
        
        $holidayData = DbManager::fetchPDOQueryData('spectra_db', $holiday_query, $holiday_params)['data'] ?? [];
        
        $holidays = [];
        $holidayDates = [];
        $holidaysByJoined = [];
        
        foreach ($holidayData as $holiday) {
            // Ensure proper date format
            $dateStr = trim($holiday['date']);
            if (strpos($dateStr, '-') !== false) {
                $date_str = $dateStr;
            } else {
                $date_str = date('Y-m-d', strtotime($dateStr));
            }
            
            $day_type = $holiday['day_type'] ?? 'holiday';
            $joined_type = $holiday['joined'] ?? 'all';
            
            $holidays[$date_str] = $day_type;
            $holidayDates[] = $date_str;
            
            if (!isset($holidaysByJoined[$joined_type])) {
                $holidaysByJoined[$joined_type] = [];
            }
            $holidaysByJoined[$joined_type][] = [
                'date' => $date_str,
                'day_type' => $day_type
            ];
        }
        
        error_log("✅ Fetched " . count($holidays) . " holidays");
        error_log("📅 Holiday Dates: " . implode(', ', array_slice($holidayDates, 0, 10)) . (count($holidayDates) > 10 ? '... and ' . (count($holidayDates) - 10) . ' more' : ''));
        
        // ========== STEP 14: MATCH EMPLOYEES WITH HOLIDAY ELIGIBILITY ==========
        $employeeHolidayEligibility = [];
        
        foreach ($employeeData as $employee) {
            $gid = $employee['gid'];
            $employee_joined = $employee['joined'] ?? '';
            
            $eligible_holidays = [];
            
            if (!empty($joined)) {
                if ($employee_joined === $joined) {
                    $eligible_holidays = $holidaysByJoined[$joined] ?? [];
                    error_log("✅ GID: $gid | Joined: '$employee_joined' | Matches filter '$joined' | Eligible holidays: " . count($eligible_holidays));
                } else {
                    error_log("❌ GID: $gid | Joined: '$employee_joined' | Does NOT match filter '$joined'");
                }
            } else {
                // No filter - get holidays for this employee's joined type
                if (isset($holidaysByJoined[$employee_joined])) {
                    $eligible_holidays = $holidaysByJoined[$employee_joined];
                } elseif (isset($holidaysByJoined['all'])) {
                    // Fallback to 'all' holidays
                    $eligible_holidays = $holidaysByJoined['all'];
                }
                error_log("ℹ️ GID: $gid | Joined: '$employee_joined' | Eligible holidays: " . count($eligible_holidays));
            }
            
            $employeeHolidayEligibility[$gid] = [
                'joined' => $employee_joined,
                'eligible_holidays' => $eligible_holidays,
                'eligible_holiday_dates' => array_column($eligible_holidays, 'date')
            ];
        }
        
        error_log("✅ Holiday eligibility mapped for " . count($employeeData) . " employees");
        
        // ========== STEP 15: FETCH OVERTIME DATA (ACTIVE EMPLOYEES ONLY) ==========
        $overtimeData = [];
        $savedRecordsCount = 0;
        $submittedRecordsCount = 0;

        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":overtime_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            // ✅ FIX: Use ot_status (OVERTIME status), not status (ATTENDANCE status)
            $overtime_query = "SELECT 
                                    ea.gid, 
                                    ea.employee_name,
                                    ea.attendance_date, 
                                    ea.overtime_hours,
                                    ea.ot_status,
                                    ea.status,
                                    ea.attendance_status,
                                    ea.created_at
                                FROM employee_attendance ea
                                WHERE ea.attendance_date BETWEEN :overtime_start_date AND :overtime_end_date
                                AND ea.gid IN (" . implode(',', $gidPlaceholders) . ")
                                AND (
                                    ea.overtime_hours IS NOT NULL 
                                    OR ea.ot_status IN ('save', 'sub')
                                )
                                ORDER BY ea.gid ASC, ea.attendance_date ASC";
            
            $overtime_params = array_merge(
                $gidParams,
                [
                    ':overtime_start_date' => $start_date,
                    ':overtime_end_date' => $end_date
                ]
            );
            
            error_log("🔍 Fetching regular overtime for ACTIVE employees");
            
            $overtime_result = DbManager::fetchPDOQueryData('spectra_db', $overtime_query, $overtime_params);
            
            if ($overtime_result === false) {
                error_log("❌ Regular Overtime query failed");
                throw new Exception('Database error: Failed to fetch overtime data');
            }
            
            $rawOvertimeData = $overtime_result['data'] ?? [];
            
            foreach ($rawOvertimeData as $record) {
                $gid = $record['gid'];
                $attendanceDate = $record['attendance_date'];
                $otStatus = $record['ot_status'];  // ✅ Use ot_status
                $attendanceStatus = $record['attendance_status'];
                
                // Get employee's holiday eligibility
                $eligibleHolidayDates = $employeeHolidayEligibility[$gid]['eligible_holiday_dates'] ?? [];
                $isHolidayForEmployee = in_array($attendanceDate, $eligibleHolidayDates);
                
                error_log("🔍 Overtime Record - GID: $gid | Date: $attendanceDate | OT_Status: $otStatus | Holiday for Employee: " . ($isHolidayForEmployee ? 'YES' : 'NO'));
                
                if ($isHolidayForEmployee) {
                    // This is a HOLIDAY for this employee - skip for regular overtime
                    error_log("   ⏭️ Skipping (will be in holiday overtime)");
                    continue;
                } else {
                    // This is a REGULAR (non-holiday) overtime
                    
                    if ($otStatus === 'sub') {
                        $record['can_submit_overtime'] = true;
                        $record['submission_blocked_reason'] = null;
                        $record['overtime_type'] = 'regular';
                        error_log("   ✅ REGULAR OT | OT_Status: 'sub' | SUBMISSION ALLOWED");
                    } else {
                        $record['ot_status'] = 'save';
                        $record['can_submit_overtime'] = false;
                        $record['overtime_type'] = 'regular';
                        $record['submission_blocked_reason'] = "Overtime can only be submitted when ot_status is 'sub'. Currently ot_status is '$otStatus'.";
                        error_log("   ⚠️ REGULAR OT | OT_Status: '$otStatus' | BLOCKED");
                    }
                    
                    $overtimeData[] = $record;
                }
            }
            
            error_log("✅ Processed " . count($rawOvertimeData) . " overtime records");
        }

        // ========== STEP 16: MAP EMPLOYEE NAMES TO REGULAR OVERTIME DATA ==========
        $employeeNameMap = [];
        foreach ($employeeData as $employee) {
            $employeeNameMap[$employee['gid']] = $employee['name'];
        }

        foreach ($overtimeData as &$overtimeRecord) {
            $gid = $overtimeRecord['gid'];
            
            if (empty($overtimeRecord['employee_name']) && isset($employeeNameMap[$gid])) {
                $overtimeRecord['employee_name'] = $employeeNameMap[$gid];
            } elseif (empty($overtimeRecord['employee_name'])) {
                $overtimeRecord['employee_name'] = "Unknown Employee ($gid)";
            }
        }

        unset($overtimeRecord);

        error_log("✅ Employee names mapped to " . count($overtimeData) . " regular overtime records");

        foreach ($overtimeData as $record) {
            if ($record['ot_status'] === 'save') {
                $savedRecordsCount++;
            } elseif ($record['ot_status'] === 'sub') {
                $submittedRecordsCount++;
            }
        }

        error_log("✅ Regular Overtime summary - Saved: $savedRecordsCount | Submitted: $submittedRecordsCount");

        // ========== STEP 17: FETCH HOLIDAY OVERTIME DATA ==========
        $holidayOvertimeData = [];
        $holidaySavedRecordsCount = 0;
        $holidaySubmittedRecordsCount = 0;

        if (!empty($employee_gids) && !empty($holidayDates)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":holiday_overtime_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $holiday_overtime_query = "SELECT 
                                        ea.gid, 
                                        ea.employee_name,
                                        ea.attendance_date, 
                                        ea.overtime_hours,
                                        ea.ot_status,
                                        ea.status,
                                        ea.attendance_status,
                                        ea.created_at,
                                        'holiday' as overtime_type
                                    FROM employee_attendance ea
                                    WHERE ea.attendance_date BETWEEN :holiday_overtime_start_date AND :holiday_overtime_end_date
                                    AND ea.gid IN (" . implode(',', $gidPlaceholders) . ")
                                    AND (
                                        ea.overtime_hours IS NOT NULL 
                                        OR ea.ot_status IN ('save', 'sub')
                                    )
                                    ORDER BY ea.gid ASC, ea.attendance_date ASC";
            
            $holiday_overtime_params = array_merge(
                $gidParams,
                [
                    ':holiday_overtime_start_date' => $start_date,
                    ':holiday_overtime_end_date' => $end_date
                ]
            );
            
            error_log("🔍 Fetching holiday overtime for ACTIVE employees");
            
            $holiday_overtime_result = DbManager::fetchPDOQueryData('spectra_db', $holiday_overtime_query, $holiday_overtime_params);
            
            if ($holiday_overtime_result === false) {
                error_log("❌ Holiday Overtime query failed");
                throw new Exception('Database error: Failed to fetch holiday overtime data');
            }
            
            $rawHolidayOvertimeData = $holiday_overtime_result['data'] ?? [];
            
            foreach ($rawHolidayOvertimeData as $record) {
                $gid = $record['gid'];
                $attendanceDate = $record['attendance_date'];
                $otStatus = $record['ot_status'];
                $attendanceStatus = $record['attendance_status'];
                
                error_log("🔍 Holiday Overtime Record - GID: $gid | Date: $attendanceDate | OT_Status: $otStatus | Attendance: $attendanceStatus");
                
                if ($otStatus === 'sub' && $attendanceStatus === 'holiday') {
                    $record['can_submit_overtime'] = true;
                    $record['submission_blocked_reason'] = null;
                    error_log("   ✅ Holiday OT | OT_Status: 'sub' | Attendance: 'holiday' | SUBMISSION ALLOWED");
                } else {
                    $record['ot_status'] = 'save';
                    $record['can_submit_overtime'] = false;
                    
                    if ($otStatus !== 'sub') {
                        $record['submission_blocked_reason'] = "Holiday overtime can only be submitted when ot_status is 'sub'. Currently ot_status is '$otStatus'.";
                        error_log("   ⚠️ Holiday OT | OT_Status: '$otStatus' | FORCING to 'save'");
                    } else {
                        $record['submission_blocked_reason'] = "Holiday overtime can only be submitted when attendance_status is 'holiday'. Currently attendance_status is '$attendanceStatus'.";
                        error_log("   ⚠️ Holiday OT | Attendance: '$attendanceStatus' | FORCING to 'save'");
                    }
                }
                
                $holidayOvertimeData[] = $record;
            }
            
            // Filter to keep ONLY holiday dates
            $holidayOvertimeData = array_filter($holidayOvertimeData, function($record) use ($holidayDates) {
                return in_array($record['attendance_date'], $holidayDates);
            });
            
            $holidayOvertimeData = array_values($holidayOvertimeData);
            
            error_log("✅ Fetched " . count($holidayOvertimeData) . " holiday overtime records");
        }

        // ========== STEP 18: MAP EMPLOYEE NAMES TO HOLIDAY OVERTIME DATA ==========
        foreach ($holidayOvertimeData as &$holidayOvertimeRecord) {
            $gid = $holidayOvertimeRecord['gid'];
            
            if (empty($holidayOvertimeRecord['employee_name']) && isset($employeeNameMap[$gid])) {
                $holidayOvertimeRecord['employee_name'] = $employeeNameMap[$gid];
            } elseif (empty($holidayOvertimeRecord['employee_name'])) {
                $holidayOvertimeRecord['employee_name'] = "Unknown Employee ($gid)";
            }
        }

        unset($holidayOvertimeRecord);

        error_log("✅ Employee names mapped to " . count($holidayOvertimeData) . " holiday overtime records");

        foreach ($holidayOvertimeData as $record) {
            if ($record['ot_status'] === 'save') {
                $holidaySavedRecordsCount++;
            } elseif ($record['ot_status'] === 'sub') {
                $holidaySubmittedRecordsCount++;
            }
        }

        error_log("✅ Holiday Overtime summary - Saved: $holidaySavedRecordsCount | Submitted: $holidaySubmittedRecordsCount");
        
        // ========== STEP 19: IDENTIFY SUPERVISOR-FILLED DATES (MODULE 19) ==========
        $supervisorFilledDates = [];
        if ($isRegularUser && !$isAdmin && !$isSupervisor) {
            $allOvertimeRecords = array_merge($overtimeData, $holidayOvertimeData);
            
            foreach ($allOvertimeRecords as $record) {
                if (!empty($record['overtime_hours']) && $record['overtime_hours'] > 0) {
                    $supervisorFilledDates[] = $record['attendance_date'];
                }
            }
            error_log("🔒 Module 19: Supervisor-filled dates: " . count($supervisorFilledDates));
        }
        
        // ========== STEP 20: FETCH TRANSFER HISTORY (ACTIVE EMPLOYEES ONLY) ==========
        $allTransferHistory = [];
        
        if (!empty($employee_gids)) {
            $gidPlaceholders = [];
            $gidParams = [];
            
            foreach ($employee_gids as $index => $gid) {
                $param_key = ":transfer_gid_$index";
                $gidParams[$param_key] = $gid;
                $gidPlaceholders[] = $param_key;
            }
            
            $transfer_query = "SELECT 
                                gid,
                                sub_department,
                                temp_sub_department,
                                transfer_from_date,
                                transfer_to_date
                            FROM employee_registration
                            WHERE gid IN (" . implode(',', $gidPlaceholders) . ")
                            AND temp_sub_department IS NOT NULL
                            AND temp_sub_department != ''
                            AND transfer_from_date IS NOT NULL
                            AND transfer_to_date IS NOT NULL
                            AND status = :transfer_status
                            ORDER BY gid ASC, transfer_to_date DESC";
            
            $transferParams = array_merge($gidParams, [':transfer_status' => 'A']);
            $allTransferHistory = DbManager::fetchPDOQueryData('spectra_db', $transfer_query, $transferParams)['data'] ?? [];
        }
        
        error_log("✅ Fetched " . count($allTransferHistory) . " transfer records");
        
        // ========== STEP 21: CALCULATE PAGE-WISE STATUS FOR EACH DATE ==========
        // ✅ FIX: USING OT_STATUS FIELD (OVERTIME STATUS), NOT STATUS FIELD (ATTENDANCE STATUS)
        $pageWiseStatusByDate = [];
        $dateRange = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            new DateTime($end_date . ' +1 day')
        );

        foreach ($dateRange as $dateObj) {
            $dateStr = $dateObj->format('Y-m-d');
            $savedCountOnPage = 0;
            $submittedCountOnPage = 0;
            $holidaySavedCountOnPage = 0;
            $holidaySubmittedCountOnPage = 0;
            $unfilledCountOnPage = 0;
            $isHoliday = in_array($dateStr, $holidayDates);
            
            $allOvertimeForDate = array_merge($overtimeData, $holidayOvertimeData);
            
            $employeeOvertimeMap = [];
            foreach ($allOvertimeForDate as $record) {
                if ($record['attendance_date'] === $dateStr) {
                    // ✅ FIX: Using ot_status (OVERTIME status), NOT status (ATTENDANCE status)
                    $employeeOvertimeMap[$record['gid']] = [
                        'ot_status' => $record['ot_status'],  // ✅ OVERTIME status
                        'overtime_type' => $record['overtime_type'] ?? 'regular'
                    ];
                }
            }

            foreach ($employeeData as $employee) {
                if (isset($employeeOvertimeMap[$employee['gid']])) {
                    // ✅ FIX: Use ot_status (OVERTIME status), NOT status (ATTENDANCE status)
                    $otStatus = $employeeOvertimeMap[$employee['gid']]['ot_status'];
                    $overtimeType = $employeeOvertimeMap[$employee['gid']]['overtime_type'];
                    
                    error_log("🔍 Date: $dateStr | GID: {$employee['gid']} | OT_Status: $otStatus | Type: $overtimeType");
                    
                    if ($overtimeType === 'holiday') {
                        if ($otStatus === 'save') {
                            $holidaySavedCountOnPage++;
                            error_log("   ✅ Holiday SAVED");
                        } elseif ($otStatus === 'sub') {
                            $holidaySubmittedCountOnPage++;
                            error_log("   ✅ Holiday SUBMITTED");
                        }
                    } else {
                        if ($otStatus === 'save') {
                            $savedCountOnPage++;
                            error_log("   ✅ Regular SAVED");
                        } elseif ($otStatus === 'sub') {
                            $submittedCountOnPage++;
                            error_log("   ✅ Regular SUBMITTED");
                        }
                    }
                } else {
                    // Only count as unfilled if it's not a holiday
                    if (!$isHoliday) {
                        $unfilledCountOnPage++;
                    }
                }
            }
            
            $totalEmployeesForDate = $savedCountOnPage + $submittedCountOnPage + $holidaySavedCountOnPage + $holidaySubmittedCountOnPage + $unfilledCountOnPage;

            $pageStatus = null;
            if ($totalEmployeesForDate > 0) {
                $totalSubmitted = $submittedCountOnPage + $holidaySubmittedCountOnPage;
                $totalSaved = $savedCountOnPage + $holidaySavedCountOnPage;
                
                error_log("📊 Date: $dateStr | Saved: $totalSaved | Submitted: $totalSubmitted | Total: $totalEmployeesForDate");
                
                // ✅ FIX: Correct logic for page status
                if ($totalSubmitted > 0 && $totalSaved === 0 && $unfilledCountOnPage === 0) {
                    // ALL SUBMITTED
                    $pageStatus = 'sub';
                    error_log("✅ Date $dateStr: ALL SUBMITTED ($totalSubmitted/$totalEmployeesForDate)");
                } elseif ($totalSaved > 0 || $totalSubmitted > 0) {
                    // PARTIALLY SAVED OR SUBMITTED
                    $pageStatus = 'save';
                    error_log("📋 Date $dateStr: SAVED (Saved: $totalSaved, Submitted: $totalSubmitted)");
                } elseif ($unfilledCountOnPage > 0) {
                    // INCOMPLETE
                    $pageStatus = 'incomplete';
                    error_log("⚠️ Date $dateStr: INCOMPLETE ($unfilledCountOnPage unfilled)");
                }
            } elseif ($isHoliday) {
                $pageStatus = 'holiday';
                error_log("📅 Date $dateStr: HOLIDAY");
            }
            
            $pageWiseStatusByDate[$dateStr] = [
                'status' => $pageStatus,
                'regular' => [
                    'saved' => $savedCountOnPage,
                    'submitted' => $submittedCountOnPage
                ],
                'holiday' => [
                    'saved' => $holidaySavedCountOnPage,
                    'submitted' => $holidaySubmittedCountOnPage
                ],
                'unfilled' => $unfilledCountOnPage,
                'total_employees_on_page_for_date' => $totalEmployeesForDate,
                'page' => $page,
                'total_pages' => $total_pages,
                'is_holiday' => $isHoliday,
                'holiday_type' => $isHoliday ? ($holidays[$dateStr] ?? 'holiday') : null,
                'joined_filter_applied' => !empty($joined) ? $joined : 'none'
            ];
        }

        error_log("✅ Page-wise status calculated for " . count($pageWiseStatusByDate) . " dates");
        
        // ========== STEP 22: RETURN SUCCESS RESPONSE ==========
        error_log("✅ RESPONSE: Returning " . count($employeeData) . " UNIQUE ACTIVE employees with overtime data");
        
        echo json_encode([
            'success' => true,
            'employees' => array_map(function($emp) use ($employeeHolidayEligibility) {
                $emp['holiday_eligibility'] = $employeeHolidayEligibility[$emp['gid']] ?? [
                    'joined' => null,
                    'eligible_holidays' => [],
                    'eligible_holiday_dates' => []
                ];
                return $emp;
            }, $employeeData),
            'overtime' => $overtimeData,
            'holiday_overtime' => $holidayOvertimeData,
            'leaves' => $allLeaveData,
            'holidays' => $holidays,
            'holidays_by_joined' => $holidaysByJoined,
            'transfer_history' => $allTransferHistory,
            'supervisor_filled_dates' => $supervisorFilledDates,
            'is_module_19' => ($isRegularUser && !$isAdmin && !$isSupervisor),
            'user_role' => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'regular_user'),
            
            'pagination' => [
                'current_page' => intval($page),
                'per_page' => intval($per_page),
                'total' => intval($total_count),
                'filtered' => intval(count($employeeData)),
                'total_pages' => intval($total_pages),
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1,
                'display_start' => intval($display_start),
                'display_end' => intval($display_end),
                'pagination_text' => $pagination_text
            ],
            
            'counts' => [
                'total_employees' => intval($total_count),
                'page_employees' => intval(count($employeeData)),
                'total_overtime_records' => intval(count($overtimeData)),
                'total_holiday_overtime_records' => intval(count($holidayOvertimeData)),
                'total_leave_records' => intval(count($allLeaveData)),
                'total_holidays' => intval(count($holidays)),
                'transfer_history_records' => intval(count($allTransferHistory)),
                'supervisor_filled_dates_count' => intval(count($supervisorFilledDates)),
                'saved_records' => intval($savedRecordsCount),
                'submitted_records' => intval($submittedRecordsCount),
                'holiday_saved_records' => intval($holidaySavedRecordsCount),
                'holiday_submitted_records' => intval($holidaySubmittedRecordsCount)
            ],
            
            'date_range' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ],
            
            'page_wise_status' => $pageWiseStatusByDate,
            
            'filters_applied' => [
                'employee_status' => 'ACTIVE (A)',
                'sub_departments' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($selected_departments) ? implode(', ', $selected_departments) : 'None'),
                'group_type' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($group_type) ? $group_type : 'None'),
                'employment_types' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($selected_employment_types) ? implode(', ', $selected_employment_types) : 'None'),
                'joined' => ($isRegularUser && !$isAdmin && !$isSupervisor) ? 'N/A (Module 19)' : (!empty($joined) ? $joined : 'All'),
                'status_filter' => $status_filter,
                'search_value' => !empty($search_value) ? $search_value : 'None',
                'search_fields' => !empty($search_value) ? implode(', ', $search_fields) : 'N/A'
            ],
            
            'status_summary' => [
                'regular_overtime' => [
                    'saved' => intval($savedRecordsCount),
                    'submitted' => intval($submittedRecordsCount),
                    'total' => intval(count($overtimeData))
                ],
                'holiday_overtime' => [
                    'saved' => intval($holidaySavedRecordsCount),
                    'submitted' => intval($holidaySubmittedRecordsCount),
                    'total' => intval(count($holidayOvertimeData))
                ],
                'combined_total' => intval(count($overtimeData) + count($holidayOvertimeData))
            ],
            
            'holiday_eligibility_summary' => [
                'total_employees_with_holidays' => count(array_filter($employeeHolidayEligibility, function($e) {
                    return !empty($e['eligible_holiday_dates']);
                })),
                'total_employees_without_holidays' => count(array_filter($employeeHolidayEligibility, function($e) {
                    return empty($e['eligible_holiday_dates']);
                })),
                'joined_filter_applied' => !empty($joined) ? $joined : 'none'
            ],
            
            'search_applied' => !empty($search_value)
        ]);
        
    } catch (Exception $e) {
        error_log("❌ EXCEPTION in fetchOvertimeData: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => $e->getMessage(),
            'page_wise_status' => [],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'filtered' => 0,
                'total_pages' => 0,
                'has_next' => false,
                'has_prev' => false,
                'display_start' => 0,
                'display_end' => 0,
                'pagination_text' => 'Error loading data'
            ]
        ]);
    }
}

private function supervisorHasAccessToSubDepartment($supervisor_gid, $sub_department) {
    $query = "SELECT DISTINCT sub_department 
              FROM employee_registration 
              WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_gid
              AND sub_department IS NOT NULL
              AND sub_department != ''";
    
    $params = [':supervisor_gid' => '%,' . $supervisor_gid . ',%'];
    
    error_log("🔍 Checking supervisor access - Supervisor ID: $supervisor_gid, Pattern: %,$supervisor_gid,%");
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
    
    if ($result !== false && isset($result['data']) && !empty($result['data'])) {
        $allowed_departments = array_column($result['data'], 'sub_department');
        error_log("📋 Supervisor $supervisor_gid has access to departments: " . implode(', ', $allowed_departments));
        
        $has_access = in_array($sub_department, $allowed_departments);
        error_log("✅ Access check result for sub_department '$sub_department': " . ($has_access ? 'ALLOWED' : 'DENIED'));
        
        return $has_access;
    }
    
    error_log("❌ No departments found for supervisor: $supervisor_gid");
    return false;
}

public function getEmployeeRegistrationData($openCloseState = null)
{    
    // ===== GET USER ROLE INFORMATION =====
    $is_admin = $_POST['is_admin'] ?? 0;
    $is_supervisor = $_POST['is_supervisor'] ?? 0;
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    
    $params = [];
    $whereClause = "WHERE (status = 'A' OR status IS NULL)";
    
    error_log("📋 getEmployeeRegistrationData - is_admin: $is_admin, is_supervisor: $is_supervisor, supervisor_id: $supervisor_id");
    
    // ===== DATE FILTER =====
    if (isset($_GET['start_date']) && isset($_GET['finish_date'])) {
        $start_date = date('Y-m-d', strtotime($_GET['start_date']));
        $finish_date = date('Y-m-d', strtotime($_GET['finish_date']));
        
        $whereClause .= " AND DATE(created_at) BETWEEN :start_date AND :finish_date";
        $params[':start_date'] = $start_date;
        $params[':finish_date'] = $finish_date;
        
        error_log("📅 Date filter applied: $start_date to $finish_date");
    }
    
    // ===== ROLE-BASED FILTERING =====
    if ($is_admin) {
        error_log("✅ Admin access - showing all employees");
    } elseif ($is_supervisor && !empty($supervisor_id)) {
        $whereClause .= " AND CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id";
        $params[':supervisor_id'] = '%,' . $supervisor_id . ',%';
        
        error_log("✅ Supervisor filter applied for supervisor: $supervisor_id");
        error_log("   Pattern: %,$supervisor_id,%");
    } else {
        // ❌ REGULAR USER: No access (or show only their own record)
        error_log("⚠️ Regular user access - restricting data");
    }

    $sql = "SELECT * FROM employee_registration 
            {$whereClause}
            ORDER BY id DESC";
    
    error_log("📋 Query: " . $sql);
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    if ($result === false || !isset($result['data'])) {
        error_log("❌ Database query failed");
        $res = [];
    } else {
        $res = $result['data'];
    }
    
    $cnt = count($res);
    $product_info = array();
    
    error_log("📊 Total employee records found: " . $cnt);
    
    if ($cnt > 0) {
        // ===== FETCH SUPERVISOR NAMES =====
        $allSupervisorGIDs = [];
        foreach ($res as $data) {
            $rawSupervisorGIDs = trim($data['supervisor']);
            if (!empty($rawSupervisorGIDs)) {
                $supervisorGIDs = array_filter(array_map('trim', explode(',', $rawSupervisorGIDs)));
                $allSupervisorGIDs = array_merge($allSupervisorGIDs, $supervisorGIDs);
            }
        }
        
        $allSupervisorGIDs = array_unique($allSupervisorGIDs);
        
        error_log("🔍 Found " . count($allSupervisorGIDs) . " unique supervisors");
        
        $supervisorNameMap = [];
        if (!empty($allSupervisorGIDs)) {
            $placeholders = implode(',', array_fill(0, count($allSupervisorGIDs), '?'));
            $nameSql = "SELECT gid, givenName, surname FROM scd_details WHERE gid IN ({$placeholders})";
            $nameResult = DbManager::fetchPDOQueryData('spectra_db', $nameSql, array_values($allSupervisorGIDs));
            
            if (!empty($nameResult['data'])) {
                foreach ($nameResult['data'] as $supData) {
                    $fullNameParts = [];
                    if (!empty($supData['givenName'])) {
                        $fullNameParts[] = trim($supData['givenName']);
                    }
                    if (!empty($supData['surname'])) {
                        $fullNameParts[] = trim($supData['surname']);
                    }
                    $fullName = implode(' ', $fullNameParts);
                    $supervisorNameMap[trim($supData['gid'])] = $fullName;
                }
                
                error_log("✅ Fetched names for " . count($supervisorNameMap) . " supervisors");
            }
        }
        
        // ===== BUILD EMPLOYEE DATA =====
        $sr = 1;
        foreach ($res as $data) {    
            $product_data['id'] = $sr;
            $sr++;
            $product_data['user_id'] = trim($data['id']);
            $product_data['gid'] = trim($data['gid']);
            $product_data['name'] = trim($data['name']);
            $product_data['department'] = trim($data['department']);
            $product_data['sub_department'] = trim($data['sub_department']);
            $product_data['sub_departmentUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['sub_department']);
            $product_data['role'] = trim($data['role']);
            $product_data['group_type'] = trim($data['group_type']);
            $product_data['in_company_manager'] = trim($data['in_company_manager']);
            $product_data['line_manager'] = trim($data['line_manager']);
            
            // ===== FORMAT SUPERVISOR NAMES =====
            $rawSupervisorGIDs = trim($data['supervisor']);
            $finalSupervisorString = '';

            if (!empty($rawSupervisorGIDs)) {
                $supervisorGIDs = array_filter(array_map('trim', explode(',', $rawSupervisorGIDs)));

                if (!empty($supervisorGIDs)) {
                    $formattedSupervisors = [];
                    foreach ($supervisorGIDs as $supGID) {
                        $supervisorName = $supervisorNameMap[$supGID] ?? ''; 
                        
                        if (!empty($supervisorName)) {
                            $formattedSupervisors[] = "{$supervisorName} ({$supGID})";
                        } else {
                            $formattedSupervisors[] = $supGID;
                        }
                    }
                    $finalSupervisorString = implode(', ', $formattedSupervisors);
                }
            }
            
            $product_data['supervisor'] = $finalSupervisorString;
            $product_data['sponsor'] = trim($data['sponsor']);
            $product_data['employment_type'] = trim($data['employment_type']);
            $product_data['joined'] = trim($data['joined']);
            $product_data['shift_type'] = trim($data['shift_type']);
            $product_data['temp_sub_department'] = trim($data['temp_sub_department']);
            $product_data['temp_sub_departmentUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['temp_sub_department']);
            $product_data['temp_group_type'] = trim($data['temp_group_type']);
            $product_data['transfer_from_date'] = trim($data['transfer_from_date']);
            $product_data['transfer_to_date'] = trim($data['transfer_to_date']);
            $product_data['status'] = trim($data['status'] ?? 'A');
            
            $product_info["data"][] = $product_data;
        }
        
        error_log("✅ Processed " . count($product_info["data"]) . " employee records");
    } else {
        $product_info["data"] = array();
        error_log("⚠️ No employee records found");
    }

    $response = [
        'draw' => $_POST['draw'] ?? 1,
        'recordsTotal' => $cnt,
        'recordsFiltered' => $cnt,
        'data' => $product_info["data"]
    ];

    error_log("📤 Response: " . count($response['data']) . " records returned");
    
    echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}


public function submitTransferRegistration()
{
    $userInfo = SharedManager::getUser();
    $user_id = $userInfo['GID'] ?? null;
    $username = $userInfo['FullName'] ?? null;

    try {
        $requiredFields = [
            'gid',
            'temp_sub_department',
            'temp_group_type',
            'from', 
            'to'   
        ];

        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: " . $field);
            }
        }

        $gid = $_POST['gid'];
        $newTempSubDepartment = $_POST['temp_sub_department'];
        $newTempGroupType = $_POST['temp_group_type'];
        $newFromDate = $_POST['from'];
        $newToDate = $_POST['to'];

        $currentDataQuery = "SELECT * FROM employee_registration WHERE gid = :gid";
        $currentDataResult = DbManager::fetchPDOQueryData('spectra_db', $currentDataQuery, [':gid' => $gid]);

        if (empty($currentDataResult['data'])) {
            throw new Exception("GID not found in employee registration. Cannot process transfer.");
        }

        $current_data = $currentDataResult['data'][0];
        $emp_id = $current_data['id']; 

        $insert_sql = "INSERT INTO employee_registration_details (
                            emp_id, gid, name, department, sub_department, role, group_type, 
                            in_company_manager, line_manager, sponsor, employment_type, shift_type, 
                            temp_sub_department, temp_group_type, transfer_from_date, transfer_to_date, 
                            joined, status, user_id, username, created_at
                        ) VALUES (
                            :emp_id, :gid, :name, :department, :sub_department, :role, :group_type, 
                            :in_company_manager, :line_manager, :sponsor, :employment_type, :shift_type, 
                            :temp_sub_department, :temp_group_type, :transfer_from_date, :transfer_to_date, 
                            :joined, :status, :user_id, :username, NOW()
                        )";
                        
        $history_params = [
            ':emp_id' => $emp_id,
            ':gid' => $current_data['gid'],
            ':name' => $current_data['name'],
            ':department' => $current_data['department'],
            ':sub_department' => $current_data['sub_department'],
            ':role' => $current_data['role'],
            ':group_type' => $current_data['group_type'],
            ':in_company_manager' => $current_data['in_company_manager'],
            ':line_manager' => $current_data['line_manager'],
            ':sponsor' => $current_data['sponsor'],
            ':employment_type' => $current_data['employment_type'],
            ':shift_type' => $current_data['shift_type'],
            ':temp_sub_department' => $current_data['temp_sub_department'],
            ':temp_group_type' => $current_data['temp_group_type'],
            ':transfer_from_date' => $current_data['transfer_from_date'],
            ':transfer_to_date' => $current_data['transfer_to_date'],
            ':joined' => $current_data['joined'],
            ':status' => $current_data['status'],
            ':user_id' => $user_id,
            ':username' => $username
        ];
        
        $history_result = DbManager::fetchPDOQuery('spectra_db', $insert_sql, $history_params);

        $query = "UPDATE employee_registration SET
                    temp_sub_department = :temp_sub_department,
                    temp_group_type = :temp_group_type,
                    transfer_from_date = :transfer_from_date,
                    transfer_to_date = :transfer_to_date
                  WHERE gid = :gid";

        $params = [
            ':gid' => $gid,
            ':temp_sub_department' => $newTempSubDepartment, 
            ':temp_group_type' => $newTempGroupType,         
            ':transfer_from_date' => $newFromDate,          
            ':transfer_to_date' => $newToDate                
        ];

        $result = DbManager::fetchPDOQuery('spectra_db', $query, $params);

        echo json_encode([
            'success' => true,
            'message' => 'User transfer details updated successfully and history logged.'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
    
public function leaveRegister()
{   
    try {
        $userInfo = SharedManager::getUser();
        $user_id = $userInfo['GID'] ?? null;
        $username = $userInfo['FullName'] ?? null;

        $error = 0;
        $gid = $_POST['gid'];
        $name = $_POST['name'];
        $department = $_POST['department'];
        $sub_department = $_POST['sub_department'];
        $role = $_POST['role'];
        $group_type = $_POST['group_type'];
        $in_company_manager = $_POST['in_company_manager'];
        $line_manager = $_POST['line_manager'];
        $supervisor_raw = $_POST['supervisor'] ?? ''; 
        $sponsor = $_POST['sponsor'];
        $employment_type = $_POST['employment_type'];
        $joined = $_POST['joined'];
        $leave_type = $_POST['leave_type'];
        $absence_detail = $_POST['absence_detail'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $total_days = $_POST['total_days'];

        $supervisor = '';
        
        // Handle empty or dash values
        if (!empty($supervisor_raw) && $supervisor_raw !== '-' && trim($supervisor_raw) !== '') {
            
            preg_match_all('/$([A-Z0-9]+)$/i', $supervisor_raw, $matches_with_parentheses);
            
            if (!empty($matches_with_parentheses[1])) {
                error_log("Found GIDs in parentheses format");
                $gids = array_map('trim', $matches_with_parentheses[1]);
                $gids = array_filter($gids);
                $gids = array_unique($gids);
                $supervisor = implode(',', $gids);
            } else {
                preg_match_all('/[A-Z]\d{4}[A-Z0-9]{3}/i', $supervisor_raw, $matches_no_parentheses);
                
                if (!empty($matches_no_parentheses[0])) {
                    error_log("Found GIDs in concatenated format (NameZ001ABC)");
                    $gids = array_map('trim', $matches_no_parentheses[0]);
                    $gids = array_filter($gids);
                    $gids = array_unique($gids);
                    $supervisor = implode(',', $gids);
                } else {
                    if (preg_match('/^[A-Z0-9,\s]+$/i', $supervisor_raw)) {
                        error_log("Already in clean GID format");
                        $supervisor = preg_replace('/\s+/', '', $supervisor_raw);
                    } else {
                        error_log("WARNING: Could not extract GIDs from supervisor field");
                    }
                }
            }
        } else {
            error_log("Supervisor field is empty, dash, or whitespace only");
        }
        
        if (!empty($supervisor)) {
            $supervisor = preg_replace('/[^A-Z0-9,]/i', '', $supervisor);
            $supervisor = preg_replace('/,+/', ',', $supervisor);
            $supervisor = trim($supervisor, ',');
        }
        
        
        if ($error == 0) {
            $sql = "INSERT INTO tbl_leave_management (
                        gid, name, department, sub_department, role, group_type, 
                        in_company_manager, line_manager, supervisor, sponsor, 
                        employment_type, joined, leave_type, absence_detail, 
                        start_date, end_date, total_days, user_id, username, created_at
                    ) VALUES (
                        :gid, :name, :department, :sub_department, :role, :group_type, 
                        :in_company_manager, :line_manager, :supervisor, :sponsor, 
                        :employment_type, :joined, :leave_type, :absence_detail, 
                        :start_date, :end_date, :total_days, :user_id, :username, NOW()
                    )";
        
            $params = [
                ":gid" => $gid,
                ":name" => $name,
                ":department" => $department,
                ":sub_department" => $sub_department,
                ":role" => $role,
                ":group_type" => $group_type,
                ":in_company_manager" => $in_company_manager,
                ":line_manager" => $line_manager,
                ":supervisor" => $supervisor, 
                ":sponsor" => $sponsor,
                ":employment_type" => $employment_type,
                ":joined" => $joined,
                ":leave_type" => $leave_type,
                ":absence_detail" => $absence_detail,
                ":start_date" => $start_date,
                ":end_date" => $end_date,
                ":total_days" => $total_days,
                ":user_id" => $user_id,
                ":username" => $username
            ];
            
            $query = DbManager::fetchPDOQuery('spectra_db', $sql, $params);
        }

        if ($query) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration Done Successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to register leave'
            ]);
        }
    } catch (Exception $e) {
        error_log("leaveRegister Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

public function updatePMSleave()
{
    try {
        // First, debug the incoming POST data
        // SharedManager::print('Received POST data:');
        // SharedManager::print($_POST);

        // Get ID first
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception("ID is required for update");
        }

        // Prepare data array with explicit keys, including id but not for updating
        $data = [
            'id' => $id,  // Include ID in data array but won't be used in SET clause
            'gid' => $_POST['gid'] ?? null,
            'name' => $_POST['name'] ?? null,
            'department' => $_POST['department'] ?? null,
            'sub_department' => $_POST['sub_department'] ?? null,
            'role' => $_POST['role'] ?? null,
            'group_type' => $_POST['group_type'] ?? null,
            'in_company_manager' => $_POST['in_company_manager'] ?? null,
            'line_manager' => $_POST['line_manager'] ?? null,
            'supervisor' => $_POST['supervisor'] ?? null,
            'sponsor' => $_POST['sponsor'] ?? null,
            'employment_type' => $_POST['employment_type'] ?? null,
            'joined' => $_POST['joined'] ?? null,
            'leave_type' => $_POST['leave_type'] ?? null,
            'absence_detail' => $_POST['absence_detail'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'total_days' => $_POST['total_days'] ?? null
        ];

        // Debug the prepared data
        // SharedManager::print('Prepared data:');
        // SharedManager::print($data);

        // SQL Update Query - Note: id is not in SET clause
        $sql = "UPDATE tbl_leave_management SET 
                gid = :gid,
                name = :name,
                department = :department,
                sub_department = :sub_department,
                role = :role,
                group_type = :group_type,
                in_company_manager = :in_company_manager,
                line_manager = :line_manager,
                supervisor = :supervisor,
                sponsor = :sponsor,
                employment_type = :employment_type,
                joined = :joined,
                leave_type = :leave_type,
                absence_detail = :absence_detail,
                start_date = :start_date,
                end_date = :end_date,
                total_days = :total_days
                WHERE id = :id";

        // Prepare parameters with proper naming, excluding id from the SET parameters
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'id') {  // Skip id in the main loop
                $params[":$key"] = $value;
            }
        }
        // Add id separately for WHERE clause
        $params[':id'] = $id;

        // Debug the final parameters
        // SharedManager::print('Final SQL parameters:');
        // SharedManager::print($params);

        // Execute query
        $stmt = DbManager::fetchPDOQuery('spectra_db', $sql, $params);

        // Return response with the unmodified ID
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Successfully Updated", 
            "code" => 200,
            "id" => $id  // Include ID in response
        ]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Error: " . $e->getMessage(), 
            "code" => 500
        ]);
    }
    exit;
}

public function updatePMSuser()
{
    try {
        // Get user information for tracking
        $userInfo = SharedManager::getUser();
        $user_id = $userInfo['GID'] ?? null;
        $username = $userInfo['FullName'] ?? null;
        
        // Get ID first
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception("ID is required for update");
        }

        // Fetch current record data - only for active users
        $query = "SELECT * FROM employee_registration WHERE id = :id AND (status = 'A' OR status IS NULL) ORDER BY id DESC";
        $select_result = DbManager::fetchPDOQuery('spectra_db', $query, [":id" => $id]);
        
        if (empty($select_result) || empty($select_result["data"])) {
            throw new Exception("Employee record not found or has been deleted");
        }
        
        // Get current data for comparison
        $current_data = $select_result["data"][0] ?? $select_result["data"];
        
        // Set default status to 'A' if not set in current data
        if (!isset($current_data['status']) || empty($current_data['status'])) {
            $current_data['status'] = 'A'; // Default to Active
        }
        
        // Prepare new data from POST
        $new_data = [
            'gid' => $_POST['gid'] ?? $current_data['gid'],
            'name' => $_POST['name'] ?? $current_data['name'],
            'department' => $_POST['department'] ?? $current_data['department'],
            'sub_department' => $_POST['sub_department'] ?? $current_data['sub_department'],
            'role' => $_POST['role'] ?? $current_data['role'],
            'group_type' => $_POST['group_type'] ?? $current_data['group_type'],
            'in_company_manager' => $_POST['in_company_manager'] ?? $current_data['in_company_manager'],
            'line_manager' => $_POST['line_manager'] ?? $current_data['line_manager'],
            'supervisor' => $_POST['supervisor'] ?? $current_data['supervisor'],
            'sponsor' => $_POST['sponsor'] ?? $current_data['sponsor'],
            'employment_type' => $_POST['employment_type'] ?? $current_data['employment_type'],
            'shift_type' => $_POST['shift_type'] ?? $current_data['shift_type'],
            'temp_sub_department' => $_POST['temp_sub_department'] ?? $current_data['temp_sub_department'],
            'temp_group_type' => $_POST['temp_group_type'] ?? $current_data['temp_group_type'],
            'joined' => $_POST['joined'] ?? $current_data['joined'],
            'transfer_from_date' => $_POST['transfer_from_date'] ?? $current_data['transfer_from_date'],
            'transfer_to_date' => $_POST['transfer_to_date'] ?? $current_data['transfer_to_date'],
            'status' => $_POST['status'] ?? $current_data['status'] // Use current status or POST value
        ];
        
        // Ensure we're not setting status to 'D' through regular updates
        if ($new_data['status'] === 'D') {
            throw new Exception("Cannot set status to deleted through update. Use the delete function instead.");
        }
        
        // Check for changes
        $has_changes = false;
        $change_details = [];
        
        foreach ($new_data as $key => $value) {
            if ($current_data[$key] != $value) {
                $has_changes = true;
                $change_details[$key] = [
                    'old' => $current_data[$key],
                    'new' => $value
                ];
            }
        }
        
        // Get current timestamp
        $current_timestamp = date('Y-m-d H:i:s');
        
        // Always insert into history table before updating - with user tracking
        $insert_sql = "INSERT INTO employee_registration_details (
                          emp_id, gid, name, department, sub_department, role, group_type, 
                          in_company_manager, line_manager, supervisor, sponsor, employment_type, 
                          shift_type, temp_sub_department, temp_group_type, transfer_from_date, 
                          transfer_to_date, joined, status, user_id, username, updated_at
                      ) VALUES (
                          :emp_id, :gid, :name, :department, :sub_department, :role, :group_type, 
                          :in_company_manager, :line_manager, :supervisor, :sponsor, :employment_type, 
                          :shift_type, :temp_sub_department, :temp_group_type, :transfer_from_date, 
                          :transfer_to_date, :joined, :status, :user_id, :username, :updated_at
                      )";
                      
        $history_params = [
            ':emp_id' => $id,
            ':gid' => $current_data['gid'],
            ':name' => $current_data['name'],
            ':department' => $current_data['department'],
            ':sub_department' => $current_data['sub_department'],
            ':role' => $current_data['role'],
            ':group_type' => $current_data['group_type'],
            ':in_company_manager' => $current_data['in_company_manager'],
            ':line_manager' => $current_data['line_manager'],
            ':supervisor' => $current_data['supervisor'],
            ':sponsor' => $current_data['sponsor'],
            ':employment_type' => $current_data['employment_type'],
            ':shift_type' => $current_data['shift_type'],
            ':temp_sub_department' => $current_data['temp_sub_department'],
            ':temp_group_type' => $current_data['temp_group_type'],
            ':joined' => $current_data['joined'],
            ':transfer_from_date' => $current_data['transfer_from_date'],
            ':transfer_to_date' => $current_data['transfer_to_date'],
            ':status' => $current_data['status'],
            ':user_id' => $user_id,
            ':username' => $username,
            ':updated_at' => $current_timestamp
        ];
        
        $history_result = DbManager::fetchPDOQuery('spectra_db', $insert_sql, $history_params);
        
        if (!$history_result) {
            throw new Exception("Failed to create history record");
        }
        
        // Only update if there are actual changes
        if ($has_changes) {
            // SQL Update Query - with user tracking
            $sql = "UPDATE employee_registration SET 
                    gid = :gid,
                    name = :name,
                    department = :department,
                    sub_department = :sub_department,
                    role = :role,
                    group_type = :group_type,
                    in_company_manager = :in_company_manager,
                    line_manager = :line_manager,
                    supervisor = :supervisor,
                    sponsor = :sponsor,
                    employment_type = :employment_type,
                    shift_type = :shift_type,
                    temp_sub_department = :temp_sub_department,
                    temp_group_type = :temp_group_type,
                    joined = :joined,
                    transfer_from_date = :transfer_from_date,
                    transfer_to_date = :transfer_to_date,
                    status = :status,
                    user_id = :user_id,
                    username = :username,
                    updated_at = :updated_at
                    WHERE id = :id AND (status = 'A' OR status IS NULL)"; // Only update active users

            $update_params = [
                ':gid' => $new_data['gid'],
                ':name' => $new_data['name'],
                ':department' => $new_data['department'],
                ':sub_department' => $new_data['sub_department'],
                ':role' => $new_data['role'],
                ':group_type' => $new_data['group_type'],
                ':in_company_manager' => $new_data['in_company_manager'],
                ':line_manager' => $new_data['line_manager'],
                ':supervisor' => $new_data['supervisor'],
                ':sponsor' => $new_data['sponsor'],
                ':employment_type' => $new_data['employment_type'],
                ':shift_type' => $new_data['shift_type'],
                ':temp_sub_department' => $new_data['temp_sub_department'],
                ':temp_group_type' => $new_data['temp_group_type'],
                ':joined' => $new_data['joined'],
                ':transfer_from_date' => $new_data['transfer_from_date'],
                ':transfer_to_date' => $new_data['transfer_to_date'],
                ':status' => $new_data['status'],
                ':user_id' => $user_id,
                ':username' => $username,
                ':updated_at' => $current_timestamp,
                ':id' => $id
            ];

            // Execute update query
            $update_result = DbManager::fetchPDOQuery('spectra_db', $sql, $update_params);
            
            if (!$update_result) {
                throw new Exception("Failed to update employee record");
            }
            
            // Check if any rows were affected
            if ($update_result['rowCount'] === 0) {
                throw new Exception("No records were updated. The user may have been deleted.");
            }
            
            $message = "Employee record successfully updated by " . $username . " (" . $user_id . ")";
        } else {
            $message = "No changes detected in employee record";
        }

        // Return response
        header('Content-Type: application/json');
        echo json_encode([
            "message" => $message, 
            "code" => 200,
            "id" => $id,
            "changes_made" => $has_changes,
            "change_count" => count($change_details),
            "change_details" => $change_details,
            "current_status" => $new_data['status'],
            "modified_by" => [
                "user_id" => $user_id,
                "username" => $username,
                "updated_at" => $current_timestamp
            ]
        ]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Error: " . $e->getMessage(), 
            "code" => 500,
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
    }
    exit;
}

public function searchEmployeeRegistration() {
    $searchTerm = $_POST['searchTerm'] ?? '';
    $filterBySupervisor = $_POST['filter_by_supervisor'] ?? false;
    $supervisorId = $_POST['supervisor_id'] ?? null;
    
    if (empty(trim($searchTerm))) {
        echo json_encode([
            "success" => false,
            "data" => [],
            "message" => "Search term is required"
        ]);
        return;
    }
    
    $searchTerm = trim($searchTerm);
    error_log("🔍 searchEmployeeRegistration initiated - Search term: '$searchTerm'");
    
    // ===== USER ROLE VALIDATION =====
    $userInfo = SharedManager::getUser();
    $isAdmin = false;
    $isSupervisor = false;
    $currentSupervisorId = null;
    
    try {
        if (isset($userInfo["Modules"]) && is_array($userInfo["Modules"])) {
            if (in_array(20, $userInfo["Modules"])) {
                $isAdmin = true;
                error_log("✅ User identified as ADMIN");
            } elseif (in_array(21, $userInfo["Modules"])) {
                $isSupervisor = true;
                $currentSupervisorId = $userInfo['GID'] ?? null;
                error_log("✅ User identified as SUPERVISOR: $currentSupervisorId");
            }
        }
    } catch (Exception $e) {
        error_log("Error validating user role: " . $e->getMessage());
    }
    
    // ===== BUILD BASE QUERY - ONLY FROM employee_registration =====
    $query = "SELECT 
                er.id,
                er.gid AS `key`,
                CONCAT(er.gid, ' - ', er.name) AS `value`,
                er.name,
                er.department,
                er.sub_department,
                er.role,
                er.group_type,
                er.in_company_manager,
                er.line_manager,
                er.supervisor,
                er.sponsor,
                er.employment_type,
                er.shift_type,
                er.temp_sub_department,
                er.temp_group_type,
                er.joined,
                er.transfer_from_date,
                er.transfer_to_date,
                er.status
              FROM employee_registration er
              WHERE (er.gid LIKE :searchTerm 
                    OR er.name LIKE :nameTerm)
              AND er.gid IS NOT NULL
              AND er.gid != ''
              AND (er.status = 'A' OR er.status IS NULL)";
    
    $params = [
        ":searchTerm" => "%$searchTerm%",
        ":nameTerm" => "%$searchTerm%"
    ];
    
    // ===== SUPERVISOR FILTERING =====
    if ($isSupervisor && !$isAdmin && ($filterBySupervisor || $supervisorId)) {
        // Verify supervisor ID matches current user
        if ($supervisorId !== $currentSupervisorId) {
            error_log("❌ Supervisor ID mismatch - Attempted: $supervisorId, Current: $currentSupervisorId");
            echo json_encode([
                "success" => false,
                "data" => [],
                "message" => "Invalid supervisor ID"
            ]);
            return;
        }
        
        // ===== ✅ UPDATED: CONCAT + LIKE pattern for supervisor filter =====
        // Filter by employees where current supervisor is in the supervisor field
        $query .= " AND CONCAT(',', TRIM(REPLACE(er.supervisor, ' ', '')), ',') LIKE :supervisor_id";
        
        $params[":supervisor_id"] = '%,' . $currentSupervisorId . ',%';
        
        error_log("✅ Supervisor {$currentSupervisorId} searching for employees");
        error_log("   Pattern: %,$currentSupervisorId,%");
        error_log("   Search term: {$searchTerm}");
    } elseif ($isSupervisor && !$isAdmin && !$filterBySupervisor && !$supervisorId) {
        // Supervisor accessing without filter parameter - restrict results
        $query .= " AND CONCAT(',', TRIM(REPLACE(er.supervisor, ' ', '')), ',') LIKE :supervisor_id";
        
        $params[":supervisor_id"] = '%,' . $currentSupervisorId . ',%';
        
        error_log("✅ Supervisor {$currentSupervisorId} searching without explicit filter");
        error_log("   Pattern: %,$currentSupervisorId,%");
        error_log("   Search term: {$searchTerm}");
    }
    
    $query .= " ORDER BY er.gid ASC
                LIMIT 50";
    
    error_log("📋 Query: " . substr($query, 0, 200) . "...");
    error_log("📋 Parameters: " . json_encode($params));
    
    try {
        $result = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
        
        if ($result === false) {
            throw new Exception('Database query failed');
        }
        
        $employeeData = $result["data"] ?? [];
        
        // ===== LOG SEARCH ACTIVITY =====
        $logMessage = "🔎 Employee search performed";
        if ($isAdmin) {
            $logMessage .= " by ADMIN";
        } elseif ($isSupervisor) {
            $logMessage .= " by SUPERVISOR {$currentSupervisorId}";
        } else {
            $logMessage .= " by USER";
        }
        error_log($logMessage . " - Search term: '{$searchTerm}', Results: " . count($employeeData));
        
        $response = [
            "success" => true,
            "data" => $employeeData,
            "count" => count($employeeData),
            "searchTerm" => $searchTerm,
            "userRole" => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'user')
        ];
        
        if (empty($employeeData)) {
            $response["message"] = "No active employees found";
            error_log("⚠️ No results returned for search term: '{$searchTerm}'");
        } else {
            error_log("✅ Successfully returned " . count($employeeData) . " employee record(s)");
        }
        
        echo json_encode($response, JSON_THROW_ON_ERROR);
    } catch (Exception $e) {
        error_log("❌ Error in searchEmployeeRegistration: " . $e->getMessage());
        error_log("   Stack trace: " . $e->getTraceAsString());
        
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage(),
            "data" => [],
            "userRole" => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'user')
        ]);
    }
}

public function getEmployeeRegistrationDetails() {
    // Set headers first
    header('Content-Type: application/json');
    
    // Prepare the default response structure
    $response = [
        "success" => false,
        "userData" => null,
        "message" => ""
    ];

    try {
        // ===== USER ROLE VALIDATION =====
        $userInfo = SharedManager::getUser();
        $isAdmin = false;
        $isSupervisor = false;
        $currentSupervisorId = null;
        
        if (isset($userInfo["Modules"]) && is_array($userInfo["Modules"])) {
            if (in_array(20, $userInfo["Modules"])) {
                $isAdmin = true;
                error_log("✅ User identified as ADMIN");
            } elseif (in_array(21, $userInfo["Modules"])) {
                $isSupervisor = true;
                $currentSupervisorId = $userInfo['GID'] ?? null;
                error_log("✅ User identified as SUPERVISOR: $currentSupervisorId");
            }
        }
        
        // Check if user has permission to access this endpoint
        if (!$isAdmin && !$isSupervisor) {
            error_log("❌ Unauthorized access attempt - No admin or supervisor role");
            throw new Exception("Insufficient permissions to access employee details");
        }
        
        // Validate input
        if (empty($_POST['gid'])) {
            error_log("❌ Missing required GID parameter");
            throw new Exception("GID is required");
        }
        
        $gid = trim($_POST['gid']);
        error_log("🔍 getEmployeeRegistrationDetails - Retrieving data for GID: $gid");
        
        // ===== SUPERVISOR AUTHORIZATION CHECK =====
        if ($isSupervisor && !$isAdmin) {
            // Verify supervisor ID match if provided in request
            $requestedSupervisorId = $_POST['supervisor_id'] ?? null;
            if ($requestedSupervisorId && $requestedSupervisorId !== $currentSupervisorId) {
                error_log("❌ Supervisor ID mismatch - Requested: $requestedSupervisorId, Current: $currentSupervisorId");
                throw new Exception("Supervisor ID mismatch - Authorization denied");
            }
            
            // ===== ✅ UPDATED: CONCAT + LIKE pattern for supervisor authorization check =====
            // Check if employee is under supervisor's management
            $authQuery = "SELECT er.gid 
                         FROM employee_registration er
                         WHERE er.gid = :gid
                         AND (
                             CONCAT(',', TRIM(REPLACE(er.supervisor, ' ', '')), ',') LIKE :supervisor_id
                             OR er.in_company_manager = :supervisor_id
                             OR er.line_manager = :supervisor_id
                         )
                         LIMIT 1";
            
            $authResult = DbManager::fetchPDOQueryData('spectra_db', $authQuery, [
                ':gid' => $gid,
                ':supervisor_id' => '%,' . $currentSupervisorId . ',%'
            ]);
            
            if (empty($authResult['data'])) {
                error_log("❌ Supervisor {$currentSupervisorId} attempted to access unauthorized employee {$gid}");
                throw new Exception("You do not have permission to access this employee's details");
            }
            
            error_log("✅ Supervisor {$currentSupervisorId} authorized to access employee {$gid}");
            error_log("   Pattern used: %,$currentSupervisorId,%");
        } else {
            error_log("✅ Admin accessing employee details for GID: {$gid}");
        }
        
        // ===== FETCH EMPLOYEE DATA =====
        $query = "SELECT er.*, 
                  CONCAT(er.gid, ' - ', er.name) as full_display_name
                  FROM employee_registration er
                  WHERE er.gid = :gid
                  LIMIT 1";
        
        error_log("📋 Executing employee fetch query for GID: $gid");
        
        $result = DbManager::fetchPDOQueryData('spectra_db', $query, [':gid' => $gid]);

        if (!empty($result['data'])) {
            $userData = $result['data'][0];
            
            error_log("✅ Employee data found for GID: $gid");
            
            // --- START: Format supervisor field with names from scd_details ---
            $formattedSupervisor = '';
            $rawSupervisorGIDs = trim($userData['supervisor']);
            
            if (!empty($rawSupervisorGIDs)) {
                error_log("🔍 Processing supervisor information - Raw GIDs: $rawSupervisorGIDs");
                
                // Split by comma and trim whitespace
                $supervisorGIDs = array_filter(array_map('trim', explode(',', $rawSupervisorGIDs)));
                
                if (!empty($supervisorGIDs)) {
                    error_log("   Found " . count($supervisorGIDs) . " supervisor(s)");
                    
                    // Fetch all supervisor details in one query
                    $placeholders = implode(',', array_fill(0, count($supervisorGIDs), '?'));
                    $supervisorQuery = "SELECT gid, givenName, surname FROM scd_details WHERE gid IN ({$placeholders})";
                    $supervisorResult = DbManager::fetchPDOQueryData('spectra_db', $supervisorQuery, $supervisorGIDs);
                    
                    // Create a map of GID to full name
                    $supervisorNameMap = [];
                    if (!empty($supervisorResult['data'])) {
                        foreach ($supervisorResult['data'] as $supData) {
                            $fullNameParts = [];
                            if (!empty($supData['givenName'])) {
                                $fullNameParts[] = trim($supData['givenName']);
                            }
                            if (!empty($supData['surname'])) {
                                $fullNameParts[] = trim($supData['surname']);
                            }
                            $fullName = implode(' ', $fullNameParts);
                            $supervisorNameMap[trim($supData['gid'])] = $fullName;
                        }
                        
                        error_log("   Retrieved names for " . count($supervisorNameMap) . " supervisor(s)");
                    }
                    
                    // Format each supervisor
                    $formattedSupervisors = [];
                    foreach ($supervisorGIDs as $supGID) {
                        $supervisorName = $supervisorNameMap[$supGID] ?? '';
                        
                        if (!empty($supervisorName)) {
                            $formattedSupervisors[] = "{$supervisorName} ({$supGID})";
                            error_log("   ✅ Formatted supervisor: {$supervisorName} ({$supGID})");
                        } else {
                            // Fallback: if name not found, just display the GID
                            $formattedSupervisors[] = $supGID;
                            error_log("   ⚠️ Name not found for supervisor GID: $supGID");
                        }
                    }
                    
                    // Join all formatted supervisors with comma
                    $formattedSupervisor = implode(', ', $formattedSupervisors);
                }
            } else {
                error_log("⚠️ No supervisors assigned to employee");
            }
            // --- END: Format supervisor field ---
            
            // ===== LOG SUCCESSFUL DATA RETRIEVAL =====
            $logMessage = "✅ Employee details retrieved";
            if ($isAdmin) {
                $logMessage .= " by ADMIN";
            } elseif ($isSupervisor) {
                $logMessage .= " by SUPERVISOR {$currentSupervisorId}";
            }
            error_log($logMessage . " - GID: {$gid}");
            
            $response = [
                "success" => true,
                "userData" => [
                    'gid' => $userData['gid'],
                    'name' => $userData['name'],
                    'department' => $userData['department'],
                    'sub_department' => $userData['sub_department'],
                    'role' => $userData['role'],
                    'group_type' => $userData['group_type'],
                    'in_company_manager' => $userData['in_company_manager'],
                    'line_manager' => $userData['line_manager'],
                    'supervisor' => $formattedSupervisor, // Use formatted supervisor
                    'supervisor_gid' => $rawSupervisorGIDs, // Also return raw GID for reference
                    'sponsor' => $userData['sponsor'],
                    'employment_type' => $userData['employment_type'],
                    'joined' => $userData['joined'],
                    'shift_type' => $userData['shift_type'] ?? '',
                    'temp_sub_department' => $userData['temp_sub_department'] ?? '',
                    'temp_group_type' => $userData['temp_group_type'] ?? '',
                    'transfer_from_date' => $userData['transfer_from_date'] ?? '',
                    'transfer_to_date' => $userData['transfer_to_date'] ?? ''
                ],
                "userRole" => $isAdmin ? 'admin' : ($isSupervisor ? 'supervisor' : 'user')
            ];
        } else {
            $response["message"] = "No data found for this GID";
            error_log("❌ Employee not found - GID: {$gid}");
        }

    } catch (Exception $e) {
        $response = [
            "success" => false,
            "message" => $e->getMessage(),
            "error" => "Authorization or data retrieval error"
        ];
        
        // Log the error
        error_log("❌ Error in getEmployeeRegistrationDetails: " . $e->getMessage());
    }
    
    // Ensure clean output
    if (ob_get_length()) ob_clean();
    
    // Encode with error checking
    $jsonResponse = json_encode($response);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Log JSON encoding error
        error_log("❌ JSON encoding error: " . json_last_error_msg());
        
        // Send a basic error response
        echo json_encode([
            "success" => false,
            "message" => "Error encoding response"
        ]);
        return;
    }
    
    echo $jsonResponse;
    exit;
}

public function getAttendanceDataWithDepartments() {
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        "success" => false,
        "data" => null,
        "daywise_data" => null,
        "departments" => [],
        "subDepartmentsByDept" => [],
        "message" => ""
    ];

    try {
        // ===== GET USER ROLE INFORMATION =====
        $isAdmin = isset($_POST['is_admin']) ? $_POST['is_admin'] === 'true' : false;
        $isSupervisor = isset($_POST['is_supervisor']) ? $_POST['is_supervisor'] === 'true' : false;
        $supervisorId = isset($_POST['supervisor_id']) ? trim($_POST['supervisor_id']) : '';
        $supervisorDepartmentsJson = isset($_POST['supervisor_departments']) ? $_POST['supervisor_departments'] : '[]';
        $supervisorDepartments = json_decode($supervisorDepartmentsJson, true);
        $includeDaywise = isset($_POST['include_daywise']) ? $_POST['include_daywise'] === 'true' : true;

        error_log("✅ getAttendanceDataWithDepartments called: isAdmin=$isAdmin, isSupervisor=$isSupervisor, supervisorId=$supervisorId");

        // ===== BUILD QUERY WITH ROLE-BASED FILTERING =====
        $whereClause = "1=1";
        $params = [];

        // **✅ SUPERVISOR FILTERING - USE NAMED PARAMETERS**
        if ($isSupervisor && !$isAdmin && !empty($supervisorId)) {
            if (!empty($supervisorDepartments) && is_array($supervisorDepartments)) {
                // ✅ Create named placeholders for each department
                $placeholders = [];
                foreach ($supervisorDepartments as $index => $dept) {
                    $paramKey = ':dept_' . $index;
                    $placeholders[] = $paramKey;
                    $params[$paramKey] = $dept;
                }
                
                $placeholderStr = implode(',', $placeholders);
                $whereClause .= " AND er.department IN ({$placeholderStr})";

                error_log("👤 Supervisor $supervisorId filtering by departments: " . implode(', ', $supervisorDepartments));
                error_log("📍 Parameters: " . json_encode($params));
            } else {
                // No departments assigned to this supervisor
                error_log("⚠️ Supervisor $supervisorId has no departments assigned");
                $response = [
                    "success" => false,
                    "data" => [],
                    "daywise_data" => [],
                    "departments" => [],
                    "subDepartmentsByDept" => [],
                    "message" => "No departments assigned to this supervisor"
                ];
                echo json_encode($response);
                exit;
            }
        }

        // ===== QUERY 1: GET MONTHLY ATTENDANCE DATA =====
        $monthlyQuery = "
            SELECT 
                er.department,
                CASE 
                    WHEN er.temp_sub_department IS NOT NULL 
                         AND er.temp_sub_department <> '' 
                    THEN er.temp_sub_department
                    ELSE er.sub_department
                END AS effective_sub_department,
                DATE_FORMAT(ea.attendance_date, '%Y-%m') AS month_year,
                CAST(SUM(ea.actual_man_hours) AS DECIMAL(10,2)) AS total_actual_man_hours,
                CAST(SUM(ea.overtime_hours) AS DECIMAL(10,2)) AS total_overtime_hours,
                COUNT(DISTINCT er.gid) AS employee_count
            FROM 
                employee_registration er
            JOIN 
                employee_attendance ea
                ON er.gid = ea.gid
            WHERE {$whereClause}
            GROUP BY 
                er.department,
                effective_sub_department,
                month_year
            ORDER BY 
                er.department,
                effective_sub_department,
                month_year
        ";

        error_log("📊 Executing monthly query");
        error_log("WHERE Clause: " . $whereClause);
        error_log("Parameters: " . json_encode($params));

        $monthlyResult = DbManager::fetchPDOQueryData('spectra_db', $monthlyQuery, $params);
        $attendanceData = !empty($monthlyResult['data']) ? $monthlyResult['data'] : [];

        // ✅ Convert numeric strings to proper floats
        foreach ($attendanceData as &$row) {
            if (isset($row['total_actual_man_hours'])) {
                $row['total_actual_man_hours'] = (float)$row['total_actual_man_hours'];
            }
            if (isset($row['total_overtime_hours'])) {
                $row['total_overtime_hours'] = (float)$row['total_overtime_hours'];
            }
            if (isset($row['employee_count'])) {
                $row['employee_count'] = (int)$row['employee_count'];
            }
        }
        unset($row);

        // ===== QUERY 2: GET DAY-WISE ATTENDANCE DATA =====
        $daywiseQuery = "
            SELECT 
                er.department,
                CASE 
                    WHEN er.temp_sub_department IS NOT NULL 
                         AND er.temp_sub_department <> '' 
                    THEN er.temp_sub_department
                    ELSE er.sub_department
                END AS effective_sub_department,
                ea.attendance_date AS date,
                DATE_FORMAT(ea.attendance_date, '%Y-%m') AS month_year,
                CAST(SUM(ea.actual_man_hours) AS DECIMAL(10,2)) AS actual_man_hours,
                CAST(SUM(ea.overtime_hours) AS DECIMAL(10,2)) AS overtime_hours,
                COUNT(DISTINCT er.gid) AS employee_count,
                GROUP_CONCAT(DISTINCT ea.attendance_status) AS attendance_statuses
            FROM 
                employee_registration er
            JOIN 
                employee_attendance ea
                ON er.gid = ea.gid
            WHERE {$whereClause}
            GROUP BY 
                er.department,
                effective_sub_department,
                ea.attendance_date
            ORDER BY 
                er.department,
                effective_sub_department,
                ea.attendance_date
        ";

        error_log("📅 Executing day-wise query");

        $daywiseResult = DbManager::fetchPDOQueryData('spectra_db', $daywiseQuery, $params);
        $daywiseData = !empty($daywiseResult['data']) ? $daywiseResult['data'] : [];

        // ✅ Convert numeric strings to proper floats
        foreach ($daywiseData as &$row) {
            if (isset($row['actual_man_hours'])) {
                $row['actual_man_hours'] = (float)$row['actual_man_hours'];
            }
            if (isset($row['overtime_hours'])) {
                $row['overtime_hours'] = (float)$row['overtime_hours'];
            }
            if (isset($row['employee_count'])) {
                $row['employee_count'] = (int)$row['employee_count'];
            }
            // Determine primary attendance status
            if (isset($row['attendance_statuses'])) {
                $statuses = explode(',', $row['attendance_statuses']);
                if (in_array('present', $statuses)) {
                    $row['attendance_status'] = 'present';
                } elseif (in_array('leave', $statuses)) {
                    $row['attendance_status'] = 'leave';
                } else {
                    $row['attendance_status'] = 'absent';
                }
                unset($row['attendance_statuses']);
            }
        }
        unset($row);

        // ===== PROCESS ATTENDANCE DATA =====
        if (!empty($attendanceData) || !empty($daywiseData)) {
            $departments = [];
            $subDepartmentsByDept = [];
            
            // Process monthly data
            foreach ($attendanceData as $row) {
                $dept = $row['department'] ?? 'Unassigned';
                $subDept = $row['effective_sub_department'] ?: 'General';
                
                if (!in_array($dept, $departments)) {
                    $departments[] = $dept;
                }
                
                if (!isset($subDepartmentsByDept[$dept])) {
                    $subDepartmentsByDept[$dept] = [];
                }
                
                if (!in_array($subDept, $subDepartmentsByDept[$dept])) {
                    $subDepartmentsByDept[$dept][] = $subDept;
                }
            }

            // Process day-wise data
            foreach ($daywiseData as $row) {
                $dept = $row['department'] ?? 'Unassigned';
                $subDept = $row['effective_sub_department'] ?: 'General';
                
                if (!in_array($dept, $departments)) {
                    $departments[] = $dept;
                }
                
                if (!isset($subDepartmentsByDept[$dept])) {
                    $subDepartmentsByDept[$dept] = [];
                }
                
                if (!in_array($subDept, $subDepartmentsByDept[$dept])) {
                    $subDepartmentsByDept[$dept][] = $subDept;
                }
            }
            
            // ===== SORT =====
            sort($departments);
            foreach ($subDepartmentsByDept as $dept => $subDepts) {
                sort($subDepartmentsByDept[$dept]);
            }
            
            $response = [
                "success" => true,
                "data" => $attendanceData,
                "daywise_data" => $daywiseData,
                "departments" => $departments,
                "subDepartmentsByDept" => $subDepartmentsByDept,
                "message" => count($attendanceData) . " monthly records and " . count($daywiseData) . " day-wise records found for " . count($departments) . " department(s)"
            ];

            error_log("✅ Successfully fetched attendance data: " . count($attendanceData) . " monthly + " . count($daywiseData) . " day-wise from " . count($departments) . " departments");

        } else {
            $response = [
                "success" => true,
                "data" => [],
                "daywise_data" => [],
                "departments" => [],
                "subDepartmentsByDept" => [],
                "message" => $isSupervisor ? "No attendance data found for your supervised departments" : "No attendance data found"
            ];

            error_log("⚠️ No attendance data found");
        }

    } catch (Exception $e) {
        $response = [
            "success" => false,
            "data" => [],
            "daywise_data" => [],
            "departments" => [],
            "subDepartmentsByDept" => [],
            "message" => "Error: " . $e->getMessage()
        ];

        error_log("❌ Error in getAttendanceDataWithDepartments: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
    }

    // ===== SEND RESPONSE =====
    if (ob_get_length()) {
        ob_clean();
    }

    $jsonResponse = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("❌ JSON encoding error: " . json_last_error_msg());
        echo json_encode([
            "success" => false,
            "data" => [],
            "daywise_data" => [],
            "departments" => [],
            "subDepartmentsByDept" => [],
            "message" => "Error encoding response: " . json_last_error_msg()
        ]);
        exit;
    }

    echo $jsonResponse;
    exit;
}

// public function getLeaveDataForEmployees()
// {
//     header('Content-Type: application/json');
    
//     try {
//         $employeeGids = isset($_POST['employee_gids']) ? json_decode($_POST['employee_gids'], true) : [];
//         $startDate = $_POST['start_date'] ?? '';
//         $endDate = $_POST['end_date'] ?? '';
        
//         error_log("getLeaveDataForEmployees called - GIDs: " . count($employeeGids) . ", Date range: $startDate to $endDate");
        
//         if (empty($employeeGids)) {
//             echo json_encode([
//                 'success' => false,
//                 'error' => 'No employee GIDs provided',
//                 'data' => []
//             ]);
//             exit;
//         }
        
//         if (empty($startDate) || empty($endDate)) {
//             echo json_encode([
//                 'success' => false,
//                 'error' => 'Start date and end date are required',
//                 'data' => []
//             ]);
//             exit;
//         }
        
//         // Fetch leave data for all employees
//         $allLeaveData = [];
        
//         foreach ($employeeGids as $gid) {
//             try {
//                 // Query to get leaves that overlap with the date range
//                 // This will return the ACTUAL leave start_date and end_date from the table
//                 $query = "
//                     SELECT 
//                         gid,
//                         leave_type,
//                         start_date,
//                         end_date,
//                         absence_detail,
//                         created_at
//                     FROM tbl_leave_management
//                     WHERE gid = ?
//                     AND (
//                         (start_date BETWEEN ? AND ?)
//                         OR (end_date BETWEEN ? AND ?)
//                         OR (start_date <= ? AND end_date >= ?)
//                     )
//                     ORDER BY start_date ASC
//                 ";
                
//                 $params = [$gid, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate];
                
//                 error_log("Executing leave query for GID: $gid");
                
//                 $result = DbManager::fetchPDOQuery('spectra_db', $query, $params);
                
//                 if (isset($result['data']) && !empty($result['data'])) {
//                     error_log("Found " . count($result['data']) . " leave records for GID: $gid");
                    
//                     // Add each leave record with its ACTUAL dates
//                     foreach ($result['data'] as $leaveRecord) {
//                         $allLeaveData[] = [
//                             'gid' => $leaveRecord['gid'],
//                             'leave_type' => $leaveRecord['leave_type'],
//                             'start_date' => $leaveRecord['start_date'], // ACTUAL leave start date
//                             'end_date' => $leaveRecord['end_date'],     // ACTUAL leave end date
//                             'absence_detail' => $leaveRecord['absence_detail'] ?? '',
//                             'created_at' => $leaveRecord['created_at'] ?? null
//                         ];
//                     }
//                 }
//             } catch (Exception $innerE) {
//                 error_log("Error fetching leave for GID $gid: " . $innerE->getMessage());
//                 continue;
//             }
//         }
        
//         error_log("Total leave records found: " . count($allLeaveData));
        
//         // Log the actual leave periods being returned
//         foreach ($allLeaveData as $leave) {
//             error_log("Leave record: GID={$leave['gid']}, Type={$leave['leave_type']}, From={$leave['start_date']} To={$leave['end_date']}");
//         }
        
//         echo json_encode([
//             'success' => true,
//             'data' => $allLeaveData,
//             'count' => count($allLeaveData),
//             'message' => count($allLeaveData) > 0 
//                 ? 'Leave data retrieved successfully' 
//                 : 'No leave records found for the selected period',
//             'table_date_range' => [
//                 'start' => $startDate,
//                 'end' => $endDate
//             ],
//             'employees_checked' => count($employeeGids)
//         ]);
//         exit;
        
//     } catch (Exception $e) {
//         error_log("Error in getLeaveDataForEmployees: " . $e->getMessage());
//         error_log("Stack trace: " . $e->getTraceAsString());
        
//         echo json_encode([
//             'success' => false,
//             'error' => $e->getMessage(),
//             'data' => [],
//             'trace' => $e->getTraceAsString()
//         ]);
//         exit;
//     }
// }


public function deleteUser()
{
    try {
        $id = $_POST['user_id'] ?? null;
        $reason = $_POST['reason'] ?? null;
        $remarks = $_POST['remarks'] ?? null;
        $status = $_POST['status'] ?? 'D';

        if (!$id) {
            throw new Exception("User ID is required");
        }
        
        if (!$reason) {
            throw new Exception("Reason is required");
        }
        
        if (!$remarks) {
            throw new Exception("Remarks are required");
        }
        
        if ($status !== 'D') {
            $status = 'D';
        }
        
        // First fetch the current user data
        $query = "SELECT * FROM employee_registration WHERE id = :id";
        $select_result = DbManager::fetchPDOQuery('spectra_db', $query, [":id" => $id]);
        
        if (empty($select_result) || empty($select_result["data"])) {
            throw new Exception("Employee record not found");
        }
        
        // Get current data
        $current_data = $select_result["data"][0] ?? $select_result["data"];
        
        // Update the current data with new values
        $current_data['status'] = $status;
        $current_data['reason'] = $reason;
        $current_data['remarks'] = $remarks;
        
        // Insert into history table
        $insert_sql = "INSERT INTO employee_registration_details (
                          emp_id, gid, name, department, sub_department, role, group_type, 
                          in_company_manager, line_manager, shift_type, temp_sub_department, 
                          temp_group_type, transfer_from_date, transfer_to_date, status,
                          reason, remarks
                      ) VALUES (
                          :emp_id, :gid, :name, :department, :sub_department, :role, :group_type, 
                          :in_company_manager, :line_manager, :shift_type, :temp_sub_department, 
                          :temp_group_type, :transfer_from_date, :transfer_to_date, :status,
                          :reason, :remarks
                      )";
                      
        $history_params = [
            ':emp_id' => $id,
            ':gid' => $current_data['gid'] ?? '',
            ':name' => $current_data['name'] ?? '',
            ':department' => $current_data['department'] ?? '',
            ':sub_department' => $current_data['sub_department'] ?? '',
            ':role' => $current_data['role'] ?? '',
            ':group_type' => $current_data['group_type'] ?? '',
            ':in_company_manager' => $current_data['in_company_manager'] ?? '',
            ':line_manager' => $current_data['line_manager'] ?? '',
            ':shift_type' => $current_data['shift_type'] ?? '',
            ':temp_sub_department' => $current_data['temp_sub_department'] ?? '',
            ':temp_group_type' => $current_data['temp_group_type'] ?? '',
            ':transfer_from_date' => $current_data['transfer_from_date'] ?? '',
            ':transfer_to_date' => $current_data['transfer_to_date'] ?? '',
            ':status' => $status,
            ':reason' => $reason,
            ':remarks' => $remarks
        ];
        
        $history_result = DbManager::fetchPDOQuery('spectra_db', $insert_sql, $history_params);
        
        if (!$history_result) {
            throw new Exception("Failed to create history record");
        }
        
        // Update the main record
        $update_sql = "UPDATE employee_registration SET status = :status, reason = :reason, remarks = :remarks WHERE id = :id";
        $update_result = DbManager::fetchPDOQuery('spectra_db', $update_sql, [
            ':status' => $status,
            ':reason' => $reason,
            ':remarks' => $remarks,
            ':id' => $id
        ]);
        
        if (!$update_result) {
            throw new Exception("Failed to update user status");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully',
            'status' => $status
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// public function getLeaveRegistrationData($openCloseState = null)
// {
//     $sql = "SELECT * FROM tbl_leave_management ORDER BY id DESC";
//     $res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
    
//     $cnt = count($res);
//     $product_info = array();
    
//     if ($cnt > 0) {
//         $sr = 1;
//         foreach ($res as $data) {    
//             $product_data['id'] = $sr;
//             $sr++;
//             $product_data['leave_id'] = trim($data['id']);
//             $product_data['gid'] = trim($data['gid']);
//             $product_data['name'] = trim($data['name']);
//             $product_data['department'] = trim($data['department']);
//             $product_data['sub_department'] = trim($data['sub_department']);
//             $product_data['sub_departmentUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['sub_department']);
//             $product_data['role'] = trim($data['role']);
//             $product_data['group_type'] = trim($data['group_type']);
//             $product_data['in_company_manager'] = trim($data['in_company_manager']);
//             $product_data['line_manager'] = trim($data['line_manager']);
//             $product_data['leave_type'] = trim($data['leave_type']);
//             $product_data['absence_detail'] = trim($data['absence_detail']);
//             $product_data['start_date'] = trim($data['start_date']);
//             $product_data['end_date'] = trim($data['end_date']);
//             $product_data['total_days'] = trim($data['total_days']);
            
//             $product_info["data"][] = $product_data;
//         }
//     } else {
//         $product_info["data"] = array();
//     }
    
//     // Add DataTables required parameters
//     $response = [
//         'draw' => $_POST['draw'] ?? 1,
//         'recordsTotal' => $cnt,
//         'recordsFiltered' => $cnt,
//         'data' => $product_info["data"]
//     ];

//     echo json_encode($response, JSON_THROW_ON_ERROR);
//     exit;
// }

public function getLeaveRegistrationData($openCloseState = null)
{    
    $params = [];
    $whereClause = "WHERE 1=1";
    
    // ===== USER ROLE & SUPERVISOR FILTERING =====
    // ✅ CHECK BOTH $_POST AND $_GET (DataTables uses POST)
    $isAdmin = $_POST['is_admin'] ?? $_GET['is_admin'] ?? 0;
    $isSupervisor = $_POST['is_supervisor'] ?? $_GET['is_supervisor'] ?? 0;
    $supervisorId = $_POST['supervisor_id'] ?? $_GET['supervisor_id'] ?? null;
    
    // ✅ CONVERT TO INTEGER - HANDLE BOTH STRING AND INT
    $isAdmin = (int)filter_var($isAdmin, FILTER_VALIDATE_INT);
    $isSupervisor = (int)filter_var($isSupervisor, FILTER_VALIDATE_INT);
    $supervisorId = !empty($supervisorId) && $supervisorId !== 'null' ? trim($supervisorId) : null;
    
    error_log("=== LEAVE DATA REQUEST ===");
    error_log("Raw values - isAdmin: " . var_export($_POST['is_admin'] ?? $_GET['is_admin'] ?? 'not set', true));
    error_log("Raw values - isSupervisor: " . var_export($_POST['is_supervisor'] ?? $_GET['is_supervisor'] ?? 'not set', true));
    error_log("Raw values - supervisorId: " . var_export($_POST['supervisor_id'] ?? $_GET['supervisor_id'] ?? 'not set', true));
    error_log("Converted values - isAdmin: $isAdmin (type: " . gettype($isAdmin) . ")");
    error_log("Converted values - isSupervisor: $isSupervisor (type: " . gettype($isSupervisor) . ")");
    error_log("Converted values - supervisorId: " . ($supervisorId ?? 'null'));
    
    // ===== ROLE-BASED FILTERING =====
    if ($isAdmin === 1) {
        // ✅ ADMIN: See all leave records (no additional filter)
        error_log("✅ ADMIN ACCESS GRANTED - Showing ALL leave records");
        // No additional WHERE clause needed
        
    } elseif ($isSupervisor === 1 && !empty($supervisorId)) {
        $whereClause .= " AND CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id";
        $params[':supervisor_id'] = '%,' . $supervisorId . ',%';
        
        error_log("✅ SUPERVISOR ACCESS - Filtering for supervisor: $supervisorId");
        error_log("   Pattern: %,$supervisorId,%");
        
    } else {
        // ❌ REGULAR USER OR INVALID REQUEST: No access
        error_log("❌ UNAUTHORIZED ACCESS - Blocking request (isAdmin=$isAdmin, isSupervisor=$isSupervisor)");
        $whereClause .= " AND 1=0"; // Return no results
    }
    
    // ===== DATE FILTERING =====
    $startDate = $_GET['start_date'] ?? $_POST['start_date'] ?? null;
    $finishDate = $_GET['finish_date'] ?? $_POST['finish_date'] ?? null;
    
    if (!empty($startDate) && !empty($finishDate)) {
        $start_date = date('Y-m-d', strtotime($startDate));
        $finish_date = date('Y-m-d', strtotime($finishDate));
        
        $whereClause .= " AND DATE(created_at) BETWEEN :start_date AND :finish_date";
        $params[':start_date'] = $start_date;
        $params[':finish_date'] = $finish_date;
        
        error_log("📅 Date filter applied: $start_date to $finish_date");
    } else {
        // ✅ DEFAULT: Show current month's data
        $whereClause .= " AND DATE(created_at) >= :first_of_month";
        $params[':first_of_month'] = date('Y-m-01');
        
        error_log("📅 Default date filter: Current month from " . date('Y-m-01'));
    }

    // ===== EXECUTE QUERY =====
    $sql = "SELECT * FROM tbl_leave_management 
            {$whereClause}
            ORDER BY id DESC";
    
    error_log("📋 Executing SQL query for leave data");
    error_log("📋 Parameters: " . json_encode($params));
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    if ($result === false) {
        error_log("❌ Database query failed");
        $res = [];
    } else {
        $res = isset($result['data']) ? $result['data'] : [];
    }

    $cnt = count($res);
    error_log("📊 Query returned $cnt records from database");
    
    $product_info = array();
    
    if ($cnt > 0) {
        // ===== FETCH ALL UNIQUE SUPERVISOR GIDs FOR BATCH LOOKUP =====
        $allSupervisorGIDs = [];
        foreach ($res as $data) {
            $rawSupervisorGIDs = trim($data['supervisor'] ?? '');
            if (!empty($rawSupervisorGIDs)) {
                $supervisorGIDs = array_filter(array_map('trim', explode(',', $rawSupervisorGIDs)));
                $allSupervisorGIDs = array_merge($allSupervisorGIDs, $supervisorGIDs);
            }
        }
        
        $allSupervisorGIDs = array_unique($allSupervisorGIDs);
        error_log("🔍 Found " . count($allSupervisorGIDs) . " unique supervisor GIDs");
        
        // ===== BATCH FETCH SUPERVISOR NAMES =====
        $supervisorNameMap = [];
        if (!empty($allSupervisorGIDs)) {
            $placeholders = implode(',', array_fill(0, count($allSupervisorGIDs), '?'));
            $supervisorQuery = "SELECT gid, givenName, surname FROM scd_details WHERE gid IN ({$placeholders})";
            
            error_log("📋 Fetching supervisor names for " . count($allSupervisorGIDs) . " GIDs");
            
            $supervisorResult = DbManager::fetchPDOQueryData('spectra_db', $supervisorQuery, array_values($allSupervisorGIDs));
            
            if (!empty($supervisorResult['data'])) {
                foreach ($supervisorResult['data'] as $supData) {
                    $fullNameParts = [];
                    if (!empty($supData['givenName'])) {
                        $fullNameParts[] = trim($supData['givenName']);
                    }
                    if (!empty($supData['surname'])) {
                        $fullNameParts[] = trim($supData['surname']);
                    }
                    $fullName = implode(' ', $fullNameParts);
                    if (!empty($fullName)) {
                        $supervisorNameMap[trim($supData['gid'])] = $fullName;
                    }
                }
                error_log("✅ Mapped " . count($supervisorNameMap) . " supervisor names");
            } else {
                error_log("⚠️ No supervisor names found in batch lookup");
            }
        }
        
        // ===== BUILD LEAVE DATA WITH SUPERVISOR NAMES =====
        $sr = 1;
        foreach ($res as $data) {    
            $product_data = []; // ✅ INITIALIZE ARRAY FOR EACH ROW
            
            $product_data['id'] = $sr;
            $sr++;
            $product_data['leave_id'] = trim($data['id'] ?? '');
            $product_data['gid'] = trim($data['gid'] ?? '');
            $product_data['name'] = trim($data['name'] ?? '');
            $product_data['department'] = trim($data['department'] ?? '');
            $product_data['sub_department'] = trim($data['sub_department'] ?? '');
            $product_data['sub_departmentUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['sub_department']);
            $product_data['role'] = trim($data['role'] ?? '');
            $product_data['group_type'] = trim($data['group_type'] ?? '');
            $product_data['in_company_manager'] = trim($data['in_company_manager'] ?? '');
            $product_data['line_manager'] = trim($data['line_manager'] ?? '');
            
            // ===== FORMAT SUPERVISOR NAMES =====
            $formattedSupervisor = '';
            $rawSupervisorGIDs = trim($data['supervisor'] ?? '');
            
            if (!empty($rawSupervisorGIDs)) {
                $supervisorGIDs = array_filter(array_map('trim', explode(',', $rawSupervisorGIDs)));
                
                if (!empty($supervisorGIDs)) {
                    $formattedSupervisors = [];
                    foreach ($supervisorGIDs as $supGID) {
                        $supervisorName = $supervisorNameMap[$supGID] ?? '';
                        
                        if (!empty($supervisorName)) {
                            $formattedSupervisors[] = "{$supervisorName} ({$supGID})";
                        } else {
                            $formattedSupervisors[] = $supGID;
                        }
                    }
                    
                    $formattedSupervisor = implode(', ', $formattedSupervisors);
                }
            }
            
            $product_data['supervisor'] = $formattedSupervisor;
            $product_data['sponsor'] = trim($data['sponsor'] ?? '');
            $product_data['employment_type'] = trim($data['employment_type'] ?? '');
            $product_data['joined'] = trim($data['joined'] ?? '');
            $product_data['leave_type'] = trim($data['leave_type'] ?? '');
            $product_data['absence_detail'] = trim($data['absence_detail'] ?? '');
            $product_data['start_date'] = trim($data['start_date'] ?? '');
            $product_data['end_date'] = trim($data['end_date'] ?? '');
            $product_data['total_days'] = trim($data['total_days'] ?? '');
            
            $product_info["data"][] = $product_data;
        }
        
        error_log("✅ Successfully built " . count($product_info["data"]) . " leave records");
    } else {
        $product_info["data"] = array();
        error_log("⚠️ No leave records found matching criteria");
    }

    // ===== RESPONSE DATA =====
    $response = [
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => $cnt,
        'recordsFiltered' => $cnt,
        'data' => $product_info["data"] ?? []
    ];

    error_log("📤 Returning response with " . count($response['data']) . " leave records");
    error_log("=== END LEAVE DATA REQUEST ===");

    echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

    private function getUserData($gid) {
        $userQuery = "SELECT 
                        gid,
                        CONCAT(IFNULL(givenName, ''), ' ', IFNULL(surname, '')) as full_name,
                        department,
                        mainFunction as role,
                        lineManager as line_manager_gid,
                        inCompanyManager as in_company_manager_gid
                      FROM scd_details
                      WHERE gid = :gid
                      LIMIT 1";
        
        $params = [":gid" => $gid];
        $userResult = DbManager::fetchPDOQueryData('spectra_db', $userQuery, $params);
        
        if ($userResult === false) {
            return false;
        }
        
        return !empty($userResult["data"]) ? $userResult["data"][0] : null;
    }
    
    private function processManagerHierarchy($userData) {
    // Initialize manager and sponsor fields
    $userData['in_company_manager_name'] = null;
    $userData['line_manager_name'] = null;
    $userData['line_manager_gid'] = null;
    $userData['sponsor_name'] = null;
    $userData['sponsor_gid'] = null;
    
    // Always fetch sponsor information regardless of in-company manager
    $sponsorQuery = "SELECT 
                        sponsor as sponsor_gid,
                        (SELECT CONCAT(IFNULL(givenName, ''), ' ', IFNULL(surname, '')) 
                         FROM scd_details 
                         WHERE gid = sponsor LIMIT 1) as sponsor_name
                    FROM scd_details 
                    WHERE gid = :gid LIMIT 1";
    
    $sponsorResult = DbManager::fetchPDOQueryData('spectra_db', $sponsorQuery, [':gid' => $userData['gid']]);
    
    if ($sponsorResult !== false && !empty($sponsorResult["data"])) {
        $userData['sponsor_gid'] = $sponsorResult["data"][0]['sponsor_gid'];
        $userData['sponsor_name'] = trim($sponsorResult["data"][0]['sponsor_name'] ?? '');
        
        // If sponsor name is empty but we have a GID, try to get the name directly
        if (empty($userData['sponsor_name']) && !empty($userData['sponsor_gid'])) {
            $sponsorNameQuery = "SELECT CONCAT(IFNULL(givenName, ''), ' ', IFNULL(surname, '')) as sponsor_name
                                FROM scd_details 
                                WHERE gid = :gid LIMIT 1";
            
            $sponsorNameResult = DbManager::fetchPDOQueryData('spectra_db', $sponsorNameQuery, [':gid' => $userData['sponsor_gid']]);
            
            if ($sponsorNameResult !== false && !empty($sponsorNameResult["data"])) {
                $userData['sponsor_name'] = trim($sponsorNameResult["data"][0]['sponsor_name'] ?? '');
            }
        }
    }
    
    // If no in-company manager, return with just the sponsor info
    if (empty($userData['in_company_manager_gid'])) {
        return $userData;
    }
    
    // Get in-company manager details
    $incompanyManagerQuery = "SELECT 
                                CONCAT(IFNULL(givenName, ''), ' ', IFNULL(surname, '')) as manager_name,
                                inCompanyManager as manager_of_in_company_manager_gid
                              FROM scd_details 
                              WHERE gid = :gid LIMIT 1";
    
    $incompanyManagerResult = DbManager::fetchPDOQueryData('spectra_db', $incompanyManagerQuery, [':gid' => $userData['in_company_manager_gid']]);
    
    if ($incompanyManagerResult === false) {
        return false;
    }
    
    if (empty($incompanyManagerResult["data"])) {
        return $userData;
    }
    
    // Set in-company manager name
    $userData['in_company_manager_name'] = trim($incompanyManagerResult["data"][0]['manager_name']);
    
    // Get line manager (manager of in-company manager)
    $managerOfIncompanyManagerGid = $incompanyManagerResult["data"][0]['manager_of_in_company_manager_gid'];
    
    if (empty($managerOfIncompanyManagerGid)) {
        $userData['line_manager_name'] = null;
        $userData['line_manager_gid'] = null;
    } else {
        $lineManagerQuery = "SELECT CONCAT(IFNULL(givenName, ''), ' ', IFNULL(surname, '')) as manager_name
                           FROM scd_details 
                           WHERE gid = :gid LIMIT 1";
        
        $lineManagerResult = DbManager::fetchPDOQueryData('spectra_db', $lineManagerQuery, [':gid' => $managerOfIncompanyManagerGid]);
        
        if ($lineManagerResult === false) {
            return false;
        }
        
        if (!empty($lineManagerResult["data"])) {
            $userData['line_manager_name'] = trim($lineManagerResult["data"][0]['manager_name']);
            $userData['line_manager_gid'] = $managerOfIncompanyManagerGid;
        } else {
            $userData['line_manager_name'] = null;
            $userData['line_manager_gid'] = null;
        }
    }
    
    // Clean up empty names
    $managerFields = ['line_manager_name', 'in_company_manager_name', 'sponsor_name'];
    foreach ($managerFields as $field) {
        if (isset($userData[$field])) {
            $userData[$field] = trim($userData[$field]);
            if (empty($userData[$field]) || $userData[$field] == ' ') {
                $userData[$field] = null;
            }
        }
    }
    
    return $userData;
}
    
    private function getDepartments() {
        $departmentsQuery = "SELECT DISTINCT department 
                            FROM scd_details 
                            WHERE department IS NOT NULL 
                            AND department != '' 
                            AND TRIM(department) != ''
                            ORDER BY department";
        
        $departmentsResult = DbManager::fetchPDOQueryData('spectra_db', $departmentsQuery, []);
        
        if ($departmentsResult === false) {
            return false;
        }
        
        $departments = [];
        if (!empty($departmentsResult["data"])) {
            foreach ($departmentsResult["data"] as $row) {
                $departments[] = $row['department'];
            }
        }
        
        return $departments;
    }
    
    private function getRoles() {
        $rolesQuery = "SELECT DISTINCT mainFunction as role
                      FROM scd_details 
                      WHERE mainFunction IS NOT NULL 
                      AND mainFunction != '' 
                      AND TRIM(mainFunction) != ''
                      ORDER BY mainFunction";
        
        $rolesResult = DbManager::fetchPDOQueryData('spectra_db', $rolesQuery, []);
        
        if ($rolesResult === false) {
            return false;
        }
        
        $roles = [];
        if (!empty($rolesResult["data"])) {
            foreach ($rolesResult["data"] as $row) {
                $roles[] = $row['role'];
            }
        }
        
        return $roles;
    }
    
    private function getManagers() {
        $managersQuery = "SELECT DISTINCT
                            CONCAT(IFNULL(givenName, ''), ' ', IFNULL(surname, '')) as manager_name
                          FROM scd_details 
                          WHERE givenName IS NOT NULL 
                          AND surname IS NOT NULL
                          AND givenName != '' 
                          AND surname != ''
                          AND TRIM(givenName) != ''
                          AND TRIM(surname) != ''
                          ORDER BY givenName, surname";
        
        $managersResult = DbManager::fetchPDOQueryData('spectra_db', $managersQuery, []);
        
        if ($managersResult === false) {
            return false;
        }
        
        $managers = [];
        if (!empty($managersResult["data"])) {
            foreach ($managersResult["data"] as $row) {
                $managerName = trim($row['manager_name']);
                if (!empty($managerName) && $managerName != ' ') {
                    $managers[] = [
                        'key' => $managerName,
                        'value' => $managerName
                    ];
                }
            }
        }
        
        return $managers;
    }

    private function getSponsors() {
    $sponsorsQuery = "SELECT DISTINCT 
                        s.gid as sponsor_gid,
                        CONCAT(IFNULL(s.givenName, ''), ' ', IFNULL(s.surname, '')) as sponsor_name
                      FROM scd_details s
                      WHERE s.gid IN (
                          SELECT DISTINCT sponsor 
                          FROM scd_details 
                          WHERE sponsor IS NOT NULL
                      )
                      AND s.givenName IS NOT NULL 
                      AND s.surname IS NOT NULL
                      AND s.givenName != '' 
                      AND s.surname != ''
                      AND TRIM(s.givenName) != ''
                      AND TRIM(s.surname) != ''
                      ORDER BY s.givenName, s.surname";
    
    $sponsorsResult = DbManager::fetchPDOQueryData('spectra_db', $sponsorsQuery, []);
    
    if ($sponsorsResult === false) {
        return false;
    }
    
    $sponsors = [];
    if (!empty($sponsorsResult["data"])) {
        foreach ($sponsorsResult["data"] as $row) {
            $sponsorName = trim($row['sponsor_name']);
            if (!empty($sponsorName) && $sponsorName != ' ') {
                $sponsors[] = [
                    'key' => $sponsorName,
                    'value' => $row['sponsor_gid']
                ];
            }
        }
    }
    
    return $sponsors;
}
    
    private function returnHttpResponse($code, $message) {
        http_response_code($code);
        echo json_encode(["error" => $message]);
        exit;
    }
    
    // Convert exception-based module check into boolean flags
    private function hasModuleAccess($moduleId) {
        try {
            SharedManager::checkAuthToModule($moduleId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>