<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
header('Content-Type:application/json;charset=utf-8;');

// Check if action is provided
if (!isset($_POST["action"]) && !isset($_GET["action"])) {
    returnHttpResponse(400, "Invalid action");
    exit;
}

$action = isset($_POST["action"]) ? $_POST["action"] : $_GET["action"];

$controller = new DocumentMasterController();

switch ($action) {
    // ✅ DOCUMENT ACTIONS
    case "getDepartments":
        $controller->getDepartments();
        break;
    case "getDocTypes":
        $controller->getDocTypes();
        break;
    case "getWITypes":              
        $controller->getWITypes();
        break;
    case "getProducts":            
        $controller->getProducts();
        break;
    case "getNextSequentialNumber":
        $controller->getNextSequentialNumber();
        break;
    case "searchOwners":
        $controller->searchOwners();
        break;
    case "saveDocument":
        $controller->saveDocument();
        break;
    case "getDocuments":
        $controller->getDocuments();
        break;
    case "getAllDocuments":
        $controller->getAllDocuments();
        break;
    case "getDocument":
        $controller->getDocument();
        break;
    case "updateDocument":
        $userModules = SharedManager::getUser()["Modules"];
        if (!in_array(23, $userModules)) {
            echo json_encode([
                'success' => false,
                'message' => 'You do not have permission to edit documents'
            ]);
            exit;
        }
        $controller->updateDocument();
        break;
    case "viewDocumentFile":
        header_remove('Content-Type');
        $controller->viewDocumentFile();
        break;
    case "downloadDocument":
        header_remove('Content-Type');
        $controller->downloadDocumentFile();
        break;
    case "downloadPdfWithWatermark":
        header_remove('Content-Type');
        $controller->downloadPdfWithWatermark();
        break;

    // ✅ CERTIFICATE ACTIONS
    case "getStandards":
        $controller->getCertificateStandards();
        break;
    case "getCertificates":
        $controller->getCertificates();
        break;
    case "getAllCertificates":
        $controller->getAllCertificates();
        break;
    case "getCertificate":
        $controller->getCertificate();
        break;
    case "saveCertificate":
        $controller->saveCertificate();
        break;
    case "updateCertificate":
        $userModules = SharedManager::getUser()["Modules"];
        if (!in_array(23, $userModules)) {
            echo json_encode([
                'success' => false,
                'message' => 'You do not have permission to edit certificates'
            ]);
            exit;
        }
        $controller->updateCertificate();
        break;

    // ✅ POLICY ACTIONS
    case "getTypes":
        $controller->getPolicyTypes();
        break;
    case "getPolicies":
        $controller->getPolicies();
        break;
    case "getAllPolicies":
        $controller->getAllPolicies();
        break;
    case "getPolicy":
        $controller->getPolicy();
        break;
    case "updatePolicy":
        $userModules = SharedManager::getUser()["Modules"];
        if (!in_array(23, $userModules)) {
            echo json_encode([
                'success' => false,
                'message' => 'You do not have permission to edit policies'
            ]);
            exit;
        }
        $controller->updatePolicy();
        break;

    // ✅ MANUAL ACTIONS
    case "getManualTypes":
        $controller->getManualTypes();
        break;
    case "getManuals":
        $controller->getManuals();
        break;
    case "getAllManuals":
        $controller->getAllManuals();
        break;
    case "getManual":
        $controller->getManual();
        break;
    case "saveManual":
        $controller->saveManual();
        break;
    case "updateManual":
        $userModules = SharedManager::getUser()["Modules"];
        if (!in_array(23, $userModules)) {
            echo json_encode([
                'success' => false,
                'message' => 'You do not have permission to edit manuals'
            ]);
            exit;
        }
        $controller->updateManual();
        break;
    case "viewManualFile":
        header_remove('Content-Type');
        $controller->viewManualFile();
        break;
    case "downloadManualPdf":
        header_remove('Content-Type');
        $controller->downloadManualPdf();
        break;

    // ✅ FAC ACTIONS
    case "getFacs":
        $controller->getFacs();
        break;
    case "getAllFacs":
        $controller->getAllFacs();
        break;
    case "getFac":
        $controller->getFac();
        break;
    case "saveFac":
        $controller->saveFac();
        break;
    case "updateFac":
        $userModules = SharedManager::getUser()["Modules"];
        if (!in_array(23, $userModules)) {
            echo json_encode([
                'success' => false,
                'message' => 'You do not have permission to edit FACs'
            ]);
            exit;
        }
        $controller->updateFac();
        break;

    default:
        returnHttpResponse(400, "Invalid action");
        break;
}
exit;

/**
 * DOCUMENT MASTER CONTROLLER
 */
class DocumentMasterController {
    private $currentUserGroupID;
    private $documentMasterTable = 'document_master';
    private $departmentTable = 'doc_department';
    private $docTypeTable = 'doc_type';
    private $scdDetailsTable = 'scd_details';
    private $uploadDir = '/../../../documentFiles/';
    private $maxFileSize = 50 * 1024 * 1024;

    public function __construct() {
        $userGroupID = SharedManager::getUser()["GroupID"];
        if (is_int($userGroupID) || (is_string($userGroupID) && ctype_digit($userGroupID))) {
            // User is authorized
        } else {
            returnHttpResponse(403, "Not Authorized");
        }
        $this->currentUserGroupID = $userGroupID;
    }

    // ========== DOCUMENT FUNCTIONS ==========

    public function getDepartments() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';
            
            $query = "SELECT id, department_name FROM " . $this->departmentTable . " WHERE 1=1";
            
            if (!empty($term)) {
                $query .= " AND department_name LIKE :term";
            }
            
            $query .= " ORDER BY department_name ASC";
            
            $params = array();
            if (!empty($term)) {
                $params[':term'] = '%' . $term . '%';
            }
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
            $departments = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $departments[] = array(
                        'id' => $row['id'],
                        'label' => $row['department_name'],
                        'value' => $row['department_name']
                    );
                }
            }

            $hardcodedDepts = ['Operations', 'QM&GCC'];
            foreach ($hardcodedDepts as $dept) {
                $exists = false;
                foreach ($departments as $existing) {
                    if ($existing['value'] === $dept) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $departments[] = array(
                        'id' => $dept,
                        'label' => $dept,
                        'value' => $dept
                    );
                }
            }

            usort($departments, function($a, $b) {
                return strcasecmp($a['label'], $b['label']);
            });

            echo json_encode($departments);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching departments: " . $e->getMessage());
        }
    }

    public function getDocTypes() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';
            
            $query = "SELECT id, doc_type FROM " . $this->docTypeTable . " WHERE 1=1";
            
            if (!empty($term)) {
                $query .= " AND doc_type LIKE :term";
            }
            
            $query .= " ORDER BY doc_type ASC";
            
            $params = array();
            if (!empty($term)) {
                $params[':term'] = '%' . $term . '%';
            }
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
            $docTypes = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $docTypes[] = array(
                        'id' => $row['id'],
                        'label' => $row['doc_type'],
                        'value' => $row['doc_type']
                    );
                }
            }

            echo json_encode($docTypes);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching document types: " . $e->getMessage());
        }
    }

    public function getWITypes() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';
            
            $query = "SELECT DISTINCT wi_type FROM " . $this->documentMasterTable . " 
                    WHERE wi_type IS NOT NULL AND wi_type != '' AND status = 1";
            
            if (!empty($term)) {
                $query .= " AND wi_type LIKE :term";
            }
            
            $query .= " ORDER BY wi_type ASC";
            
            $params = array();
            if (!empty($term)) {
                $params[':term'] = '%' . $term . '%';
            }
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
            $wiTypes = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $wiTypes[] = array(
                        'label' => $row['wi_type'],
                        'value' => $row['wi_type']
                    );
                }
            }

            echo json_encode($wiTypes);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching WI types: " . $e->getMessage());
        }
    }

    public function getProducts() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';
            
            $query = "SELECT DISTINCT product FROM " . $this->documentMasterTable . " 
                    WHERE product IS NOT NULL AND product != '' AND status = 1";
            
            if (!empty($term)) {
                $query .= " AND product LIKE :term";
            }
            
            $query .= " ORDER BY product ASC";
            
            $params = array();
            if (!empty($term)) {
                $params[':term'] = '%' . $term . '%';
            }
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
            $products = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $products[] = array(
                        'label' => $row['product'],
                        'value' => $row['product']
                    );
                }
            }

            echo json_encode($products);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching products: " . $e->getMessage());
        }
    }

    public function searchOwners() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';

            if (empty($term) || strlen($term) < 2) {
                echo json_encode([]);
                return;
            }

            $hardcodedOwners = [
                'Mandar Vilankar',
                'Shouvik Bhattacharyya',
                'Prafulla Mahajan',
                'Vidya Hasurkar',
                'Ketan More',
                'Akhilesh Patel',
                'Nachiket Dabhadkar',
                'Toushif Shaikh',
                'Shailesh Patil',
                'Rakesh Jangra',
                'Ratnakar Lad',
                'Shantaram Ambike',
                'Harshad Borole',
                'Prashant Deshmukh',
                'Darshana Gandhi',
                'Shailesh Taware',
                'Kalpesh Shinde',
                'Amol Pawar',
                'Sagar Shaha',
                'Pralay More',
                'Smruti Rajeshirke',
                'Anil Motwani',
                'Dhananjay Singh Baghel',
                'Pradeep Goriwale',
                'Yasir Mustafa Abidi'
            ];

            $query = "SELECT DISTINCT CONCAT(givenName, ' ', preferredSurname) as full_name 
                    FROM " . $this->scdDetailsTable . " 
                    WHERE (givenName LIKE :term OR preferredSurname LIKE :term)
                    AND givenName IS NOT NULL 
                    AND givenName != ''
                    AND preferredSurname IS NOT NULL 
                    AND preferredSurname != ''
                    ORDER BY givenName ASC, preferredSurname ASC
                    LIMIT 20";

            $response = DbManager::fetchPDOQueryData('spectra_db', $query, [
                ':term' => '%' . $term . '%'
            ]);

            $owners = array();
            $ownerNames = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $fullName = $row['full_name'];
                    if (!in_array($fullName, $ownerNames)) {
                        $owners[] = array(
                            'full_name' => $fullName,
                            'source' => 'database'
                        );
                        $ownerNames[] = $fullName;
                    }
                }
            }

            foreach ($hardcodedOwners as $hardcodedName) {
                if (stripos($hardcodedName, $term) !== false) {
                    if (!in_array($hardcodedName, $ownerNames)) {
                        $owners[] = array(
                            'full_name' => $hardcodedName,
                            'source' => 'hardcoded'
                        );
                        $ownerNames[] = $hardcodedName;
                    }
                }
            }

            usort($owners, function($a, $b) {
                return strcasecmp($a['full_name'], $b['full_name']);
            });

            $finalOwners = array();
            foreach ($owners as $owner) {
                $finalOwners[] = array(
                    'full_name' => $owner['full_name']
                );
            }

            echo json_encode($finalOwners);
        } catch (Exception $e) {
            error_log("Error searching owners: " . $e->getMessage());
            echo json_encode([]);
        }
    }

    public function getNextSequentialNumber() {
        try {
            $department = isset($_POST['department']) ? $_POST['department'] : '';
            $doc_type = isset($_POST['doc_type']) ? $_POST['doc_type'] : '';

            if (empty($department) || empty($doc_type)) {
                returnHttpResponse(400, "Department and Doc Type are required");
            }

            $query = "SELECT MAX(CAST(seq_no AS UNSIGNED)) as max_seq FROM " . $this->documentMasterTable . " 
                      WHERE department = :department 
                      AND doc_type = :doc_type 
                      AND status = 1";

            $response = DbManager::fetchPDOQueryData('spectra_db', $query, [
                ':department' => $department,
                ':doc_type' => $doc_type
            ]);

            $nextSeq = 1;
            if (isset($response['data']) && !empty($response['data'])) {
                $row = $response['data'][0];
                if ($row['max_seq'] !== null && $row['max_seq'] !== '') {
                    $nextSeq = (int)$row['max_seq'] + 1;
                }
            }

            echo json_encode(array(
                'success' => true,
                'next_seq_no' => $nextSeq
            ));
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching sequential number: " . $e->getMessage());
        }
    }

    public function saveDocument() {
        try {
            error_log("===== saveDocument START =====");
            error_log("Document Type: " . (isset($_POST['type']) ? $_POST['type'] : 'NOT SET'));
            
            $documentType = isset($_POST['type']) ? trim($_POST['type']) : '';
            error_log("Processing document type: " . $documentType);

            switch ($documentType) {
                case "policy":
                    $this->savePolicy();
                    break;
                case 'document':
                    $this->saveDocumentType($documentType);
                    break;
                case 'certificate':
                    $this->saveCertificateType($documentType);
                    break;
                case 'manual':
                    $this->saveManualType($documentType);
                    break;
                case 'fac':
                    $this->saveFACType($documentType);
                    break;
                default:
                    echo json_encode(array(
                        'success' => false,
                        'message' => 'Invalid document type: ' . $documentType
                    ));
                    break;
            }

            error_log("===== saveDocument END =====");

        } catch (Exception $e) {
            error_log("Exception in saveDocument: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ));
        }
    }

    private function saveDocumentType($documentType) {
        try {
            error_log("→ Processing DOCUMENT type");

            $department = isset($_POST['department']) ? trim($_POST['department']) : '';
            $doc_type = isset($_POST['doc_type']) ? trim($_POST['doc_type']) : '';
            $seq_no = isset($_POST['seq_no']) ? (int)$_POST['seq_no'] : 0;
            $version = isset($_POST['version']) ? trim($_POST['version']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $owner = isset($_POST['owner']) ? trim($_POST['owner']) : '';
            $issue_date = isset($_POST['issue_date']) ? trim($_POST['issue_date']) : '';
            $next_review_date = isset($_POST['next_review_date']) ? trim($_POST['next_review_date']) : '';
            $document_number = isset($_POST['document_number']) ? trim($_POST['document_number']) : '';
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
            $expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
            $wi_type = isset($_POST['wi_type']) ? trim($_POST['wi_type']) : null;
            $product = isset($_POST['product']) ? trim($_POST['product']) : null;

            error_log("Data extracted - Department: $department, DocType: $doc_type, SeqNo: $seq_no");

            $convertedIssueDate = !empty($issue_date) ? $this->convertDateFormatFlexible($issue_date) : null;
            $convertedNextReviewDate = !empty($next_review_date) ? $this->convertDateFormatFlexible($next_review_date) : null;
            $convertedExpiryDate = !empty($expiry_date) ? $this->convertDateFormatFlexible($expiry_date) : null;

            if (!empty($issue_date) && !$convertedIssueDate) {
                error_log("Invalid issue date format: $issue_date");
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            if (!empty($next_review_date) && !$convertedNextReviewDate) {
                error_log("Invalid next review date format: $next_review_date");
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid next review date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            if (!empty($expiry_date) && !$convertedExpiryDate) {
                error_log("Invalid expiry date format: $expiry_date");
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid expiry date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $document_number);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            }

            if (!empty($department)) {
                $this->addDepartmentIfNew($department);
            }
            if (!empty($doc_type)) {
                $this->addDocTypeIfNew($doc_type);
            }

            $formattedSeqNo = str_pad($seq_no, 3, '0', STR_PAD_LEFT);

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, department, doc_type, seq_no, version, description, owner, issue_date, next_review_date, document_number, remark, wi_type, product, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :department, :doc_type, :seq_no, :version, :description, :owner, :issue_date, :next_review_date, :document_number, :remark, :wi_type, :product, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => $documentType,
                ':department' => $department,
                ':doc_type' => $doc_type,
                ':seq_no' => $formattedSeqNo,
                ':version' => $version,
                ':description' => $description,
                ':owner' => $owner,
                ':issue_date' => $convertedIssueDate,
                ':next_review_date' => $convertedNextReviewDate,
                ':document_number' => $document_number,
                ':remark' => $remark,
                ':wi_type' => !empty($wi_type) ? $wi_type : null,
                ':product' => !empty($product) ? $product : null,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ Document saved successfully");
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Document saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ));
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode(array(
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving document. Please try again.'
                ));
            }

        } catch (Exception $e) {
            error_log("Exception in saveDocumentType: " . $e->getMessage());
            echo json_encode(array(
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ));
        }
    }

    private function saveCertificateType($documentType) {
        try {
            error_log("→ Processing CERTIFICATE type");

            $standard = isset($_POST['standard']) ? trim($_POST['standard']) : '';
            $certificate_no = isset($_POST['certificate_no']) ? trim($_POST['certificate_no']) : '';
            $cert_issue_date = isset($_POST['cert_issue_date']) ? trim($_POST['cert_issue_date']) : '';
            $cert_expiry_date = isset($_POST['cert_expiry_date']) ? trim($_POST['cert_expiry_date']) : '';
            $cert_remark = isset($_POST['cert_remark']) ? trim($_POST['cert_remark']) : '';

            error_log("Data extracted - Standard: $standard, CertNo: $certificate_no");

            $convertedIssueDate = !empty($cert_issue_date) ? $this->convertDateFormatFlexible($cert_issue_date) : null;
            $convertedExpiryDate = !empty($cert_expiry_date) ? $this->convertDateFormatFlexible($cert_expiry_date) : null;

            if (!empty($cert_issue_date) && !$convertedIssueDate) {
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            if (!empty($cert_expiry_date) && !$convertedExpiryDate) {
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid expiry date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['cert_pdf_file']) && $_FILES['cert_pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['cert_pdf_file'], $certificate_no);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            }

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, doc_type, certificate_no, issue_date, expiry_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :doc_type, :certificate_no, :issue_date, :expiry_date, :remark, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => $documentType,
                ':doc_type' => $standard,
                ':certificate_no' => $certificate_no,
                ':issue_date' => $convertedIssueDate,
                ':expiry_date' => $convertedExpiryDate,
                ':remark' => $cert_remark,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ Certificate saved successfully");
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Certificate saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ));
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode(array(
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving certificate. Please try again.'
                ));
            }

        } catch (Exception $e) {
            error_log("Exception in saveCertificateType: " . $e->getMessage());
            echo json_encode(array(
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ));
        }
    }

    private function saveManualType($documentType) {
        try {
            error_log("→ Processing MANUAL type");

            $manual_type = isset($_POST['manual_type']) ? trim($_POST['manual_type']) : '';
            $manual_issue_date = isset($_POST['manual_issue_date']) ? trim($_POST['manual_issue_date']) : '';
            $version = isset($_POST['manual_version']) ? trim($_POST['manual_version']) : '0.0';
            $manual_remark = isset($_POST['manual_remark']) ? trim($_POST['manual_remark']) : '';

            error_log("Data extracted - ManualType: $manual_type, VersionNo: $version");

            $convertedIssueDate = !empty($manual_issue_date) ? $this->convertDateFormatFlexible($manual_issue_date) : null;

            if (!empty($manual_issue_date) && !$convertedIssueDate) {
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['manual_pdf_file']) && $_FILES['manual_pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['manual_pdf_file'], $manual_type);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            } else if (isset($_FILES['manual_pdf_file']) && $_FILES['manual_pdf_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Error uploading PDF file. Please try again.'
                ));
                return;
            }

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, doc_type, version, issue_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :doc_type, :version, :issue_date, :remark, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => $documentType,
                ':doc_type' => $manual_type,
                ':version' => $version,
                ':issue_date' => $convertedIssueDate,
                ':remark' => $manual_remark,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ Manual saved successfully");
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Manual saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ));
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode(array(
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving manual. Please try again.'
                ));
            }

        } catch (Exception $e) {
            error_log("Exception in saveManualType: " . $e->getMessage());
            echo json_encode(array(
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ));
        }
    }

    private function saveFACType($documentType) {
        try {
            error_log("→ Processing FAC type");

            $department = isset($_POST['fac_department']) ? trim($_POST['fac_department']) : '';
            $issue_date = isset($_POST['fac_issue_date']) ? trim($_POST['fac_issue_date']) : '';
            $next_review_date = isset($_POST['fac_next_review_date']) ? trim($_POST['fac_next_review_date']) : '';
            $remark = isset($_POST['fac_remark']) ? trim($_POST['fac_remark']) : '';

            error_log("Data extracted - Department: $department, Issue Date: $issue_date, Next Review: $next_review_date");

            if (empty($department)) {
                error_log("Missing department");
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Department is required'
                ));
                return;
            }

            if (empty($issue_date)) {
                error_log("Missing issue date");
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Issue date is required'
                ));
                return;
            }

            if (empty($next_review_date)) {
                error_log("Missing next review date");
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Next review date is required'
                ));
                return;
            }

            $convertedIssueDate = !empty($issue_date) ? $this->convertDateFormatFlexible($issue_date) : null;
            $convertedNextReviewDate = !empty($next_review_date) ? $this->convertDateFormatFlexible($next_review_date) : null;

            if (!empty($issue_date) && !$convertedIssueDate) {
                error_log("Invalid issue date format: $issue_date");
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            if (!empty($next_review_date) && !$convertedNextReviewDate) {
                error_log("Invalid next review date format: $next_review_date");
                echo json_encode(array(
                    'success' => false,
                    'message' => "Invalid next review date format. Please use DD/MM/YYYY format."
                ));
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['fac_pdf_file']) && $_FILES['fac_pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['fac_pdf_file'], $department);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            }

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, department, issue_date, next_review_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :department, :issue_date, :next_review_date, :remark, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => $documentType,
                ':department' => $department,
                ':issue_date' => $convertedIssueDate,
                ':next_review_date' => $convertedNextReviewDate,
                ':remark' => $remark,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            error_log("Insert Query: " . $insertQuery);
            error_log("Insert Params: " . json_encode($insertParams));

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ FAC saved successfully");
                echo json_encode(array(
                    'success' => true,
                    'message' => 'FAC saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ));
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode(array(
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving FAC. Please try again.'
                ));
            }

            error_log("===== saveFACType END =====");

        } catch (Exception $e) {
            error_log("Exception in saveFACType: " . $e->getMessage());
            echo json_encode(array(
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ));
        }
    }

    // ✅ FIXED: getDocuments() - Always sort by create_date DESC
    public function getDocuments() {
        try {
            $draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
            $start = isset($_POST['start']) ? $_POST['start'] : 0;
            $length = isset($_POST['length']) ? $_POST['length'] : 10;
            $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

            $filterDepartment = isset($_POST['department']) ? trim($_POST['department']) : '';
            $filterDocType = isset($_POST['doc_type']) ? trim($_POST['doc_type']) : '';
            $filterWIType = isset($_POST['wi_type']) ? trim($_POST['wi_type']) : '';
            $filterProduct = isset($_POST['product']) ? trim($_POST['product']) : '';
            $filterDocumentNumber = isset($_POST['document_number']) ? trim($_POST['document_number']) : '';
            $filterOwner = isset($_POST['owner']) ? trim($_POST['owner']) : '';
            $filterDateFrom = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
            $filterDateTo = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

            $params = [];

            $sql = "SELECT * FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $params[':type'] = 'document';

            if (!empty($filterDepartment)) {
                $sql .= " AND department = :department";
                $params[':department'] = $filterDepartment;
            }

            if (!empty($filterDocType)) {
                $sql .= " AND doc_type = :doc_type";
                $params[':doc_type'] = $filterDocType;
            }

            if (!empty($filterWIType)) {
                $sql .= " AND wi_type = :wi_type";
                $params[':wi_type'] = $filterWIType;
            }

            if (!empty($filterProduct)) {
                $sql .= " AND product = :product";
                $params[':product'] = $filterProduct;
            }

            if (!empty($filterDocumentNumber)) {
                $sql .= " AND document_number LIKE :document_number";
                $params[':document_number'] = '%' . $filterDocumentNumber . '%';
            }

            if (!empty($filterOwner)) {
                $sql .= " AND owner LIKE :owner";
                $params[':owner'] = '%' . $filterOwner . '%';
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            if (!empty($search)) {
                $sql .= " AND (department LIKE :search 
                         OR doc_type LIKE :search 
                         OR document_number LIKE :search 
                         OR description LIKE :search 
                         OR owner LIKE :search
                         OR wi_type LIKE :search
                         OR product LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $totalSql = "SELECT COUNT(*) as count FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $totalParams = [':type' => 'document'];
            $totalRecords = DbManager::fetchPDOQueryData('spectra_db', $totalSql, $totalParams)['data'][0]['count'];

            $countSql = "SELECT COUNT(*) as count FROM (" . $sql . ") as counted";
            $filteredRecords = DbManager::fetchPDOQueryData('spectra_db', $countSql, $params)['data'][0]['count'];

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $sql .= " LIMIT :start, :length";
            $params[':start'] = (int)$start;
            $params[':length'] = (int)$length;

            error_log("Final SQL: " . $sql);

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            $response = [
                'draw' => (int)$draw,
                'recordsTotal' => (int)$totalRecords,
                'recordsFiltered' => (int)$filteredRecords,
                'data' => $result['data'] ?? []
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching documents: " . $e->getMessage());
        }
    }

    public function getAllDocuments() {
        try {
            $filterDepartment = isset($_POST['department']) ? trim($_POST['department']) : '';
            $filterDocType = isset($_POST['doc_type']) ? trim($_POST['doc_type']) : '';
            $filterWIType = isset($_POST['wi_type']) ? trim($_POST['wi_type']) : '';
            $filterProduct = isset($_POST['product']) ? trim($_POST['product']) : '';
            $filterDocumentNumber = isset($_POST['document_number']) ? trim($_POST['document_number']) : '';
            $filterOwner = isset($_POST['owner']) ? trim($_POST['owner']) : '';
            $filterDateFrom = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
            $filterDateTo = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

            $params = [];

            $sql = "SELECT * FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $params[':type'] = 'document';

            if (!empty($filterDepartment)) {
                $sql .= " AND department = :department";
                $params[':department'] = $filterDepartment;
            }

            if (!empty($filterDocType)) {
                $sql .= " AND doc_type = :doc_type";
                $params[':doc_type'] = $filterDocType;
            }

            if (!empty($filterWIType)) {
                $sql .= " AND wi_type = :wi_type";
                $params[':wi_type'] = $filterWIType;
            }

            if (!empty($filterProduct)) {
                $sql .= " AND product = :product";
                $params[':product'] = $filterProduct;
            }

            if (!empty($filterDocumentNumber)) {
                $sql .= " AND document_number LIKE :document_number";
                $params[':document_number'] = '%' . $filterDocumentNumber . '%';
            }

            if (!empty($filterOwner)) {
                $sql .= " AND owner LIKE :owner";
                $params[':owner'] = '%' . $filterOwner . '%';
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            if (isset($result['data']) && !empty($result['data'])) {
                echo json_encode($result['data']);
            } else {
                echo json_encode([]);
            }
        } catch (Exception $e) {
            error_log("Error in getAllDocuments: " . $e->getMessage());
            echo json_encode([]);
        }
    }

    public function getDocument() {
        try {
            $id = isset($_POST['id']) ? $_POST['id'] : null;

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Document ID is required'
                ]);
                return;
            }

            $query = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1";
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, [':id' => $id]);

            if (isset($response['data']) && !empty($response['data'])) {
                $document = $response['data'][0];
                
                $hasExistingFile = (!empty($document['file_path']) && trim($document['file_path']) !== '') 
                                && (!empty($document['actual_file_name']) && trim($document['actual_file_name']) !== '');
                
                $document['hasExistingFile'] = $hasExistingFile;
                
                echo json_encode([
                    'success' => true,
                    'data' => $document
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Document not found'
                ]);
            }
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching document: " . $e->getMessage());
        }
    }

    public function updateDocument() {
        try {
            error_log("===== updateDocument START =====");
            
            $requiredFields = array('id', 'department', 'doc_type', 'seq_no', 'version', 'owner', 'issue_date', 'document_number', 'description');
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    echo json_encode([
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                    ]);
                    return;
                }
            }

            $id = (int)$_POST['id'];
            
            // ✅ FETCH ORIGINAL DATA FOR SMART UPDATE
            $getOldQuery = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id";
            $oldData = DbManager::fetchPDOQueryData('spectra_db', $getOldQuery, [':id' => $id]);

            if (empty($oldData['data'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Document not found'
                ]);
                return;
            }

            $oldDocument = $oldData['data'][0];

            $department = trim($_POST['department']);
            $doc_type = trim($_POST['doc_type']);
            $seq_no = (int)$_POST['seq_no'];
            $version = trim($_POST['version']);
            $owner = trim($_POST['owner']);
            $issue_date = trim($_POST['issue_date']);
            $next_review_date = isset($_POST['next_review_date']) ? trim($_POST['next_review_date']) : '';
            $document_number = trim($_POST['document_number']);
            $description = trim($_POST['description']);
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
            $expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
            $wi_type = isset($_POST['wi_type']) ? trim($_POST['wi_type']) : null;
            $product = isset($_POST['product']) ? trim($_POST['product']) : null;

            error_log("Document ID: " . $id . ", Document Number: " . $document_number);

            $convertedDate = $this->convertDateFormatFlexible($issue_date);
            if (!$convertedDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $convertedNextReviewDate = !empty($next_review_date) ? $this->convertDateFormatFlexible($next_review_date) : null;
            if (!empty($next_review_date) && !$convertedNextReviewDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid next review date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $convertedExpiryDate = !empty($expiry_date) ? $this->convertDateFormatFlexible($expiry_date) : null;
            if (!empty($expiry_date) && !$convertedExpiryDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid expiry date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $hasDocumentFile = isset($_FILES['document_file']) && 
                              $_FILES['document_file']['error'] === UPLOAD_ERR_OK &&
                              $_FILES['document_file']['size'] > 0;

            error_log("Document File Present: " . ($hasDocumentFile ? 'YES' : 'NO'));

            // ✅ TRACK CHANGED FIELDS
            $changedFields = [];
            $formattedSeqNo = str_pad($seq_no, 3, '0', STR_PAD_LEFT);

            if ($department !== $oldDocument['department']) {
                $changedFields['department'] = $department;
            }
            if ($doc_type !== $oldDocument['doc_type']) {
                $changedFields['doc_type'] = $doc_type;
            }
            if ($formattedSeqNo !== $oldDocument['seq_no']) {
                $changedFields['seq_no'] = $formattedSeqNo;
            }
            if ($version !== $oldDocument['version']) {
                $changedFields['version'] = $version;
            }
            if ($owner !== $oldDocument['owner']) {
                $changedFields['owner'] = $owner;
            }
            if ($convertedDate !== $oldDocument['issue_date']) {
                $changedFields['issue_date'] = $convertedDate;
            }
            if ($convertedNextReviewDate !== $oldDocument['next_review_date']) {
                $changedFields['next_review_date'] = $convertedNextReviewDate;
            }
            if ($document_number !== $oldDocument['document_number']) {
                $changedFields['document_number'] = $document_number;
            }
            if ($description !== $oldDocument['description']) {
                $changedFields['description'] = $description;
            }
            if ($remark !== ($oldDocument['remark'] ?? '')) {
                $changedFields['remark'] = $remark;
            }
            if ($convertedExpiryDate !== ($oldDocument['expiry_date'] ?? null)) {
                $changedFields['expiry_date'] = $convertedExpiryDate;
            }
            if ($wi_type !== ($oldDocument['wi_type'] ?? null)) {
                $changedFields['wi_type'] = $wi_type;
            }
            if ($product !== ($oldDocument['product'] ?? null)) {
                $changedFields['product'] = $product;
            }

            if (!$hasDocumentFile) {
                error_log("Info: No file uploaded - keeping existing file");
                
                // ✅ SMART UPDATE: Only update if there are changes
                if (empty($changedFields)) {
                    echo json_encode([
                        'success' => true,
                        'message' => "No changes detected. Document remains unchanged."
                    ]);
                    error_log("===== updateDocument END (NO CHANGES) =====");
                    return;
                }

                $updateQuery = "UPDATE " . $this->documentMasterTable . " SET ";
                $updateParams = [':id' => $id];
                
                $updateParts = [];
                foreach ($changedFields as $field => $value) {
                    $updateParts[] = "$field = :$field";
                    $updateParams[":$field"] = $value;
                }
                
                $updateParts[] = "update_date = NOW()";
                $updateQuery .= implode(', ', $updateParts) . " WHERE id = :id";

                $updateResult = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);

                if ($updateResult['status'] === 'success' || (isset($updateResult['data']) && $updateResult['data'] > 0)) {
                    error_log("Document updated successfully without file change. Changed fields: " . count($changedFields));
                    
                    echo json_encode([
                        'success' => true,
                        'message' => "Document updated successfully! " . count($changedFields) . " field(s) changed.",
                        'changedFieldsCount' => count($changedFields)
                    ]);
                } else {
                    error_log("Database update failed");
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error updating document'
                    ]);
                }
                
                error_log("===== updateDocument END =====");
                return;
            }

            $newFilePath = null;
            $newActualFileName = null;

            error_log("Processing document file: " . $_FILES['document_file']['name']);
            $uploadResult = $this->handleFileUpload($_FILES['document_file'], $document_number);
            
            if (!$uploadResult['success']) {
                error_log("Document upload failed: " . $uploadResult['message']);
                echo json_encode($uploadResult);
                return;
            }
            
            $newFilePath = $uploadResult['file_path'];
            $newActualFileName = $uploadResult['actual_file_name'];
            error_log("Document uploaded successfully");

            $archiveQuery = "INSERT INTO document_master_archive 
                            (dm_id, department, doc_type, seq_no, version, owner, issue_date, next_review_date, document_number, description, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:dm_id, :department, :doc_type, :seq_no, :version, :owner, :issue_date, :next_review_date, :document_number, :description, :remark, :file_path, :actual_file_name, :status, :create_date, :update_date)";

            $archiveParams = [
                ':dm_id' => $id,
                ':department' => $oldDocument['department'],
                ':doc_type' => $oldDocument['doc_type'],
                ':seq_no' => $oldDocument['seq_no'],
                ':version' => $oldDocument['version'],
                ':owner' => $oldDocument['owner'],
                ':issue_date' => $oldDocument['issue_date'],
                ':next_review_date' => isset($oldDocument['next_review_date']) ? $oldDocument['next_review_date'] : null,
                ':document_number' => $oldDocument['document_number'],
                ':description' => $oldDocument['description'],
                ':remark' => $oldDocument['remark'],
                ':file_path' => $oldDocument['file_path'],
                ':actual_file_name' => isset($oldDocument['actual_file_name']) ? $oldDocument['actual_file_name'] : '',
                ':status' => $oldDocument['status'],
                ':create_date' => $oldDocument['create_date'],
                ':update_date' => $oldDocument['update_date']
            ];

            $archiveResult = DbManager::fetchPDOQuery('spectra_db', $archiveQuery, $archiveParams);

            if (!$archiveResult) {
                if ($newFilePath) {
                    $this->deleteFile($newFilePath);
                }
                error_log("Archive failed");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error archiving old document'
                ]);
                return;
            }

            error_log("Document archived successfully");

            // ✅ UPDATE WITH FILE CHANGE
            $changedFields['file_path'] = $newFilePath;
            $changedFields['actual_file_name'] = $newActualFileName;

            $updateQuery = "UPDATE " . $this->documentMasterTable . " SET ";
            $updateParams = [':id' => $id];
            
            $updateParts = [];
            foreach ($changedFields as $field => $value) {
                $updateParts[] = "$field = :$field";
                $updateParams[":$field"] = $value;
            }
            
            $updateParts[] = "update_date = NOW()";
            $updateQuery .= implode(', ', $updateParts) . " WHERE id = :id";

            $updateResult = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);

            if ($updateResult['status'] === 'success' || (isset($updateResult['data']) && $updateResult['data'] > 0)) {
                error_log("Document updated successfully with new file. Changed fields: " . count($changedFields));
                
                echo json_encode([
                    'success' => true,
                    'message' => "Document updated successfully with version " . $version . "! Old version archived and new file added.",
                    'changedFieldsCount' => count($changedFields)
                ]);
            } else {
                if ($newFilePath) {
                    $this->deleteFile($newFilePath);
                }
                
                error_log("Database update failed");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating document'
                ]);
            }
            
            error_log("===== updateDocument END =====");

        } catch (Exception $e) {
            error_log("Exception in updateDocument: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function viewDocumentFile() {
        try {
            error_log("========== VIEW DOCUMENT FILE START ==========");
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', 1);
            }
            ini_set('zlib.output_compression', 'Off');
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $documentId = isset($input['document_id']) ? (int)$input['document_id'] : null;

            error_log("[VIEW] DocumentID: " . ($documentId ? $documentId : 'NULL'));

            if (!$documentId) {
                error_log("[VIEW] ERROR: Missing document ID");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Document ID required']);
                exit;
            }

            $userModules = SharedManager::getUser()["Modules"];
            $hasViewRights = in_array(22, $userModules);
            $hasAdminRights = in_array(23, $userModules);

            if (!$hasViewRights && !$hasAdminRights) {
                error_log("[VIEW] ERROR: Access denied");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            $docQuery = "SELECT file_path, actual_file_name FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1";
            $docResult = DbManager::fetchPDOQueryData('spectra_db', $docQuery, [':id' => $documentId]);
            
            if (empty($docResult['data'])) {
                error_log("[VIEW] ERROR: Document not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Document not found']);
                exit;
            }

            $filePath = $docResult['data'][0]['file_path'];
            $actualFileName = $docResult['data'][0]['actual_file_name'];

            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            $realPath = realpath($fullPath);
            $uploadRealPath = realpath($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir);

            error_log("[VIEW] Full path: " . $fullPath);
            error_log("[VIEW] Real path: " . ($realPath ? $realPath : 'NULL'));

            if ($realPath === false || strpos($realPath, $uploadRealPath) !== 0) {
                error_log("[VIEW] ERROR: Security check failed");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            if (!file_exists($realPath) || !is_file($realPath)) {
                error_log("[VIEW] ERROR: File not found at " . $realPath);
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File not found']);
                exit;
            }

            $fileExtension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            
            $validExtensions = ['pdf', 'ppt', 'pptx', 'xlsx', 'xls', 'doc', 'docx'];
            if (!in_array($fileExtension, $validExtensions)) {
                error_log("[VIEW] ERROR: Invalid file type");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid file format']);
                exit;
            }

            $mimeTypes = [
                'pdf' => 'application/pdf',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls' => 'application/vnd.ms-excel',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];

            $mimeType = isset($mimeTypes[$fileExtension]) ? $mimeTypes[$fileExtension] : 'application/octet-stream';

            $fileSize = filesize($realPath);
            
            if ($fileSize === false || $fileSize <= 0) {
                error_log("[VIEW] ERROR: Cannot determine file size");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot read file']);
                exit;
            }

            error_log("[VIEW] File: " . $actualFileName . ", Size: " . $fileSize . " bytes, Type: " . $mimeType);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType, true);
            header('Content-Disposition: inline; filename="' . basename($actualFileName) . '"', true);
            header('Content-Length: ' . $fileSize, true);
            header('Cache-Control: no-cache, no-store, must-revalidate', true);
            header('Pragma: no-cache', true);
            header('Expires: 0', true);
            header('Accept-Ranges: bytes', true);

            flush();

            $chunkSize = 1024 * 1024;
            $handle = fopen($realPath, 'rb');

            if (!$handle) {
                error_log("[VIEW] ERROR: Cannot open file");
                http_response_code(500);
                exit;
            }

            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false) {
                    error_log("[VIEW] ERROR: Error reading file chunk");
                    break;
                }
                echo $chunk;
                flush();
                
                if (connection_aborted()) {
                    error_log("[VIEW] Connection aborted by client");
                    break;
                }
            }

            fclose($handle);
            error_log("[VIEW] ✓ File sent for viewing - " . $actualFileName);
            error_log("========== VIEW DOCUMENT FILE END ==========");
            exit;

        } catch (Exception $e) {
            error_log("[VIEW] EXCEPTION: " . $e->getMessage());
            error_log("[VIEW] Stack trace: " . $e->getTraceAsString());
            
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }
    }

    public function downloadDocumentFile() {
        try {
            error_log("========== DOWNLOAD DOCUMENT FILE START ==========");
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', 1);
            }
            ini_set('zlib.output_compression', 'Off');
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $documentId = isset($input['document_id']) ? (int)$input['document_id'] : null;

            error_log("[DOWNLOAD] DocumentID: " . ($documentId ? $documentId : 'NULL'));

            if (!$documentId) {
                error_log("[DOWNLOAD] ERROR: Missing document ID");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Document ID required']);
                exit;
            }

            $userModules = SharedManager::getUser()["Modules"];
            $hasViewRights = in_array(22, $userModules);
            $hasAdminRights = in_array(23, $userModules);

            if (!$hasViewRights && !$hasAdminRights) {
                error_log("[DOWNLOAD] ERROR: Access denied");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            $docQuery = "SELECT file_path, actual_file_name FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1";
            $docResult = DbManager::fetchPDOQueryData('spectra_db', $docQuery, [':id' => $documentId]);
            
            if (empty($docResult['data'])) {
                error_log("[DOWNLOAD] ERROR: Document not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Document not found']);
                exit;
            }

            $filePath = $docResult['data'][0]['file_path'];
            $actualFileName = $docResult['data'][0]['actual_file_name'];

            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            $realPath = realpath($fullPath);
            $uploadRealPath = realpath($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir);

            if ($realPath === false || strpos($realPath, $uploadRealPath) !== 0) {
                error_log("[DOWNLOAD] ERROR: Security check failed");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            if (!file_exists($realPath) || !is_file($realPath)) {
                error_log("[DOWNLOAD] ERROR: File not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File not found']);
                exit;
            }

            $fileExtension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            
            $validExtensions = ['pdf', 'ppt', 'pptx', 'xlsx', 'xls', 'doc', 'docx'];
            if (!in_array($fileExtension, $validExtensions)) {
                error_log("[DOWNLOAD] ERROR: Invalid file type");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid file format']);
                exit;
            }

            $mimeTypes = [
                'pdf' => 'application/pdf',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls' => 'application/vnd.ms-excel',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];

            $mimeType = isset($mimeTypes[$fileExtension]) ? $mimeTypes[$fileExtension] : 'application/octet-stream';

            $fileSize = filesize($realPath);
            
            if ($fileSize === false || $fileSize <= 0) {
                error_log("[DOWNLOAD] ERROR: Cannot determine file size");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot read file']);
                exit;
            }

            error_log("[DOWNLOAD] File: " . $actualFileName . ", Size: " . $fileSize);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType, true);
            header('Content-Disposition: attachment; filename="' . basename($actualFileName) . '"', true);
            header('Content-Length: ' . $fileSize, true);
            header('Cache-Control: no-cache, no-store, must-revalidate', true);
            header('Pragma: no-cache', true);
            header('Expires: 0', true);
            header('Connection: close', true);
            header('Accept-Ranges: bytes', true);

            flush();

            $chunkSize = 1024 * 1024;
            $handle = fopen($realPath, 'rb');

            if (!$handle) {
                error_log("[DOWNLOAD] ERROR: Cannot open file");
                http_response_code(500);
                exit;
            }

            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false) {
                    error_log("[DOWNLOAD] ERROR: Error reading file chunk");
                    break;
                }
                echo $chunk;
                flush();
                
                if (connection_aborted()) {
                    error_log("[DOWNLOAD] Connection aborted by client");
                    break;
                }
            }

            fclose($handle);
            error_log("[DOWNLOAD] ✓ Download completed - " . $actualFileName);
            error_log("========== DOWNLOAD DOCUMENT FILE END ==========");
            exit;

        } catch (Exception $e) {
            error_log("[DOWNLOAD] EXCEPTION: " . $e->getMessage());
            error_log("[DOWNLOAD] Stack trace: " . $e->getTraceAsString());
            
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }
    }

    public function downloadPdfWithWatermark() {
        try {
            error_log("========== DOWNLOAD PDF WITH WATERMARK START ==========");
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', 1);
            }
            ini_set('zlib.output_compression', 'Off');
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $documentId = isset($input['document_id']) ? (int)$input['document_id'] : null;

            error_log("[WATERMARK] DocumentID: " . ($documentId ? $documentId : 'NULL'));

            if (!$documentId) {
                error_log("[WATERMARK] ERROR: Missing document ID");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Document ID required']);
                exit;
            }

            $userModules = SharedManager::getUser()["Modules"];
            $hasViewRights = in_array(22, $userModules);
            $hasAdminRights = in_array(23, $userModules);

            error_log("[WATERMARK] User Permissions - ViewRights: " . ($hasViewRights ? 'YES' : 'NO') . ", AdminRights: " . ($hasAdminRights ? 'YES' : 'NO'));

            if (!$hasViewRights && !$hasAdminRights) {
                error_log("[WATERMARK] ERROR: Access denied");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            $docQuery = "SELECT file_path, actual_file_name FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1";
            $docResult = DbManager::fetchPDOQueryData('spectra_db', $docQuery, [':id' => $documentId]);
            
            if (empty($docResult['data'])) {
                error_log("[WATERMARK] ERROR: Document not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Document not found']);
                exit;
            }

            $filePath = $docResult['data'][0]['file_path'];
            $actualFileName = $docResult['data'][0]['actual_file_name'];

            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            $realPath = realpath($fullPath);
            $uploadRealPath = realpath($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir);

            error_log("[WATERMARK] Full path: " . $fullPath);
            error_log("[WATERMARK] Real path: " . ($realPath ? $realPath : 'NULL'));

            if ($realPath === false || strpos($realPath, $uploadRealPath) !== 0) {
                error_log("[WATERMARK] ERROR: Security check failed");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            if (!file_exists($realPath) || !is_file($realPath)) {
                error_log("[WATERMARK] ERROR: File not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File not found']);
                exit;
            }

            $fileExtension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

            error_log("[WATERMARK] File extension: " . $fileExtension);

            if ($fileExtension !== 'pdf') {
                error_log("[WATERMARK] ERROR: Not a PDF file");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Only PDF files support watermarking']);
                exit;
            }

            if (!$this->validatePdfFile($realPath)) {
                error_log("[WATERMARK] ERROR: Invalid PDF file");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid or corrupted PDF file']);
                exit;
            }

            error_log("[WATERMARK] ✓ PDF validation passed");

            $pdfData = file_get_contents($realPath);
            if (!$pdfData) {
                error_log("[WATERMARK] ERROR: Cannot read PDF file");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot read file']);
                exit;
            }

            $pdfSize = strlen($pdfData);
            error_log("[WATERMARK] PDF size: " . $pdfSize . " bytes");

            $applyWatermark = !$hasAdminRights;

            error_log("[WATERMARK] Apply Watermark: " . ($applyWatermark ? 'YES' : 'NO (Admin User)'));

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf', true);
            header('Content-Disposition: attachment; filename="' . basename($actualFileName) . '"', true);
            header('Content-Length: ' . $pdfSize, true);
            header('Cache-Control: no-cache, no-store, must-revalidate', true);
            header('Pragma: no-cache', true);
            header('Expires: 0', true);
            header('Connection: close', true);

            header('X-Has-Admin-Rights: ' . ($hasAdminRights ? 'true' : 'false'), true);
            header('X-Apply-Watermark: ' . ($applyWatermark ? 'true' : 'false'), true);

            error_log("[WATERMARK] ✓ Headers sent successfully");

            echo $pdfData;
            
            error_log("[WATERMARK] ✓ Original PDF sent to client");
            error_log("[WATERMARK] Client will " . ($applyWatermark ? "apply CONTROLLED COPY watermark" : "NOT apply watermark (Admin)"));
            error_log("========== DOWNLOAD PDF WITH WATERMARK END ==========");
            exit;

        } catch (Exception $e) {
            error_log("[WATERMARK] EXCEPTION: " . $e->getMessage());
            error_log("[WATERMARK] Stack trace: " . $e->getTraceAsString());
            
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }
    }

    // ========== CERTIFICATE FUNCTIONS ==========

    public function getCertificateStandards() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';
            
            $query = "SELECT DISTINCT doc_type FROM " . $this->documentMasterTable . " 
                    WHERE type = 'certificate' AND doc_type IS NOT NULL AND doc_type != '' AND status = 1";
            
            if (!empty($term)) {
                $query .= " AND doc_type LIKE :term";
            }
            
            $query .= " ORDER BY doc_type ASC";
            
            $params = array();
            if (!empty($term)) {
                $params[':term'] = '%' . $term . '%';
            }
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
            $standards = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $standards[] = array(
                        'value' => $row['doc_type'],
                        'label' => $row['doc_type']
                    );
                }
            }

            echo json_encode($standards);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching certificate standards: " . $e->getMessage());
        }
    }

    // ✅ FIXED: getCertificates() - Always sort by create_date DESC
    public function getCertificates() {
        try {
            $draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
            $start = isset($_POST['start']) ? $_POST['start'] : 0;
            $length = isset($_POST['length']) ? $_POST['length'] : 10;
            $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

            $filterStandard = isset($_POST['standard']) ? trim($_POST['standard']) : '';
            $filterDateFrom = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
            $filterDateTo = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

            $params = [];

            $sql = "SELECT * FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $params[':type'] = 'certificate';

            if (!empty($filterStandard)) {
                $sql .= " AND doc_type = :standard";
                $params[':standard'] = $filterStandard;
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            if (!empty($search)) {
                $sql .= " AND (doc_type LIKE :search OR certificate_no LIKE :search OR remark LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $totalSql = "SELECT COUNT(*) as count FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $totalParams = [':type' => 'certificate'];
            $totalRecords = DbManager::fetchPDOQueryData('spectra_db', $totalSql, $totalParams)['data'][0]['count'];

            $countSql = "SELECT COUNT(*) as count FROM (" . $sql . ") as counted";
            $filteredRecords = DbManager::fetchPDOQueryData('spectra_db', $countSql, $params)['data'][0]['count'];

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $sql .= " LIMIT :start, :length";
            $params[':start'] = (int)$start;
            $params[':length'] = (int)$length;

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            $response = [
                'draw' => (int)$draw,
                'recordsTotal' => (int)$totalRecords,
                'recordsFiltered' => (int)$filteredRecords,
                'data' => $result['data'] ?? []
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching certificates: " . $e->getMessage());
        }
    }

    public function getAllCertificates() {
        try {
            $filterStandard = isset($_POST['standard']) ? trim($_POST['standard']) : '';
            $filterDateFrom = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
            $filterDateTo = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

            $params = [];

            $sql = "SELECT * FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $params[':type'] = 'certificate';

            if (!empty($filterStandard)) {
                $sql .= " AND doc_type = :standard";
                $params[':standard'] = $filterStandard;
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            if (isset($result['data']) && !empty($result['data'])) {
                echo json_encode($result['data']);
            } else {
                echo json_encode([]);
            }
        } catch (Exception $e) {
            error_log("Error in getAllCertificates: " . $e->getMessage());
            echo json_encode([]);
        }
    }

    public function getCertificate() {
        try {
            $id = isset($_POST['id']) ? $_POST['id'] : null;

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Certificate ID is required'
                ]);
                return;
            }

            $query = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1 AND type = 'certificate'";
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, [':id' => $id]);

            if (isset($response['data']) && !empty($response['data'])) {
                $certificate = $response['data'][0];
                
                $hasExistingFile = (!empty($certificate['file_path']) && trim($certificate['file_path']) !== '') 
                                && (!empty($certificate['actual_file_name']) && trim($certificate['actual_file_name']) !== '');
                
                $certificate['hasExistingFile'] = $hasExistingFile;
                
                echo json_encode([
                    'success' => true,
                    'data' => $certificate
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Certificate not found'
                ]);
            }
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching certificate: " . $e->getMessage());
        }
    }

    public function saveCertificate() {
        try {
            error_log("===== saveCertificate START =====");

            $standard = isset($_POST['standard']) ? trim($_POST['standard']) : '';
            $certificate_no = isset($_POST['certificate_no']) ? trim($_POST['certificate_no']) : '';
            $issue_date = isset($_POST['issue_date']) ? trim($_POST['issue_date']) : '';
            $expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : '';
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

            error_log("Data extracted - Standard: $standard, CertNo: $certificate_no");

            if (empty($standard)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Standard is required'
                ]);
                return;
            }

            if (empty($certificate_no)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Certificate Number is required'
                ]);
                return;
            }

            if (empty($issue_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Issue Date is required'
                ]);
                return;
            }

            if (empty($expiry_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Expiry Date is required'
                ]);
                return;
            }

            $convertedIssueDate = !empty($issue_date) ? $this->convertDateFormatFlexible($issue_date) : null;
            $convertedExpiryDate = !empty($expiry_date) ? $this->convertDateFormatFlexible($expiry_date) : null;

            if (!empty($issue_date) && !$convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            if (!empty($expiry_date) && !$convertedExpiryDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid expiry date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            if ($convertedExpiryDate && $convertedIssueDate && $convertedExpiryDate <= $convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Expiry date must be after issue date"
                ]);
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $certificate_no);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'PDF file is required'
                ]);
                return;
            }

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, doc_type, certificate_no, issue_date, expiry_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :doc_type, :certificate_no, :issue_date, :expiry_date, :remark, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => 'certificate',
                ':doc_type' => $standard,
                ':certificate_no' => $certificate_no,
                ':issue_date' => $convertedIssueDate,
                ':expiry_date' => $convertedExpiryDate,
                ':remark' => $remark,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ Certificate saved successfully");
                echo json_encode([
                    'success' => true,
                    'message' => 'Certificate saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ]);
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode([
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving certificate. Please try again.'
                ]);
            }

            error_log("===== saveCertificate END =====");

        } catch (Exception $e) {
            error_log("Exception in saveCertificate: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCertificate() {
        try {
            error_log("===== updateCertificate START =====");
            
            $requiredFields = array('id', 'standard', 'certificate_no', 'issue_date', 'expiry_date');
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    echo json_encode([
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                    ]);
                    return;
                }
            }

            $id = (int)$_POST['id'];
            
            // ✅ FETCH ORIGINAL DATA FOR SMART UPDATE
            $getOldQuery = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id AND type = 'certificate'";
            $oldData = DbManager::fetchPDOQueryData('spectra_db', $getOldQuery, [':id' => $id]);

            if (empty($oldData['data'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Certificate not found'
                ]);
                return;
            }

            $oldCertificate = $oldData['data'][0];

            $standard = trim($_POST['standard']);
            $certificate_no = trim($_POST['certificate_no']);
            $issue_date = trim($_POST['issue_date']);
            $expiry_date = trim($_POST['expiry_date']);
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

            error_log("Certificate ID: " . $id . ", CertNo: " . $certificate_no);

            $convertedIssueDate = $this->convertDateFormatFlexible($issue_date);
            if (!$convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $convertedExpiryDate = $this->convertDateFormatFlexible($expiry_date);
            if (!$convertedExpiryDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid expiry date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            if ($convertedExpiryDate <= $convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Expiry date must be after issue date"
                ]);
                return;
            }

            $hasFile = isset($_FILES['pdf_file']) && 
                      $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK &&
                      $_FILES['pdf_file']['size'] > 0;

            error_log("File Present: " . ($hasFile ? 'YES' : 'NO'));

            // ✅ TRACK CHANGED FIELDS
            $changedFields = [];

            if ($standard !== $oldCertificate['doc_type']) {
                $changedFields['doc_type'] = $standard;
            }
            if ($certificate_no !== $oldCertificate['certificate_no']) {
                $changedFields['certificate_no'] = $certificate_no;
            }
            if ($convertedIssueDate !== $oldCertificate['issue_date']) {
                $changedFields['issue_date'] = $convertedIssueDate;
            }
            if ($convertedExpiryDate !== $oldCertificate['expiry_date']) {
                $changedFields['expiry_date'] = $convertedExpiryDate;
            }
            if ($remark !== ($oldCertificate['remark'] ?? '')) {
                $changedFields['remark'] = $remark;
            }

            $newFilePath = null;
            $newActualFileName = null;

            if ($hasFile) {
                error_log("Processing certificate file: " . $_FILES['pdf_file']['name']);
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $certificate_no);
                
                if (!$uploadResult['success']) {
                    error_log("Certificate upload failed: " . $uploadResult['message']);
                    echo json_encode($uploadResult);
                    return;
                }
                
                $newFilePath = $uploadResult['file_path'];
                $newActualFileName = $uploadResult['actual_file_name'];
                error_log("Certificate uploaded successfully");

                $archiveQuery = "INSERT INTO document_master_archive 
                                (dm_id, type, doc_type, certificate_no, issue_date, expiry_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                                VALUES 
                                (:dm_id, :type, :doc_type, :certificate_no, :issue_date, :expiry_date, :remark, :file_path, :actual_file_name, :status, :create_date, :update_date)";

                $archiveParams = [
                    ':dm_id' => $id,
                    ':type' => $oldCertificate['type'],
                    ':doc_type' => $oldCertificate['doc_type'],
                    ':certificate_no' => $oldCertificate['certificate_no'],
                    ':issue_date' => $oldCertificate['issue_date'],
                    ':expiry_date' => $oldCertificate['expiry_date'],
                    ':remark' => $oldCertificate['remark'],
                    ':file_path' => $oldCertificate['file_path'],
                    ':actual_file_name' => isset($oldCertificate['actual_file_name']) ? $oldCertificate['actual_file_name'] : '',
                    ':status' => $oldCertificate['status'],
                    ':create_date' => $oldCertificate['create_date'],
                    ':update_date' => $oldCertificate['update_date']
                ];

                $archiveResult = DbManager::fetchPDOQuery('spectra_db', $archiveQuery, $archiveParams);

                if (!$archiveResult) {
                    if ($newFilePath) {
                        $this->deleteFile($newFilePath);
                    }
                    error_log("Archive failed");
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error archiving old certificate'
                    ]);
                    return;
                }

                error_log("Certificate archived successfully");

                $changedFields['file_path'] = $newFilePath;
                $changedFields['actual_file_name'] = $newActualFileName;
            }

            // ✅ SMART UPDATE: Only update if there are changes
            if (empty($changedFields)) {
                echo json_encode([
                    'success' => true,
                    'message' => "No changes detected. Certificate remains unchanged."
                ]);
                error_log("===== updateCertificate END (NO CHANGES) =====");
                return;
            }

            $updateQuery = "UPDATE " . $this->documentMasterTable . " SET ";
            $updateParams = [':id' => $id];
            
            $updateParts = [];
            foreach ($changedFields as $field => $value) {
                $updateParts[] = "$field = :$field";
                $updateParams[":$field"] = $value;
            }
            
            $updateParts[] = "update_date = NOW()";
            $updateQuery .= implode(', ', $updateParts) . " WHERE id = :id";

            $updateResult = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);

            if ($updateResult['status'] === 'success' || (isset($updateResult['data']) && $updateResult['data'] > 0)) {
                error_log("Certificate updated successfully. Changed fields: " . count($changedFields));
                
                echo json_encode([
                    'success' => true,
                    'message' => "Certificate updated successfully! " . count($changedFields) . " field(s) changed.",
                    'changedFieldsCount' => count($changedFields)
                ]);
            } else {
                if ($newFilePath) {
                    $this->deleteFile($newFilePath);
                }
                
                error_log("Database update failed");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating certificate'
                ]);
            }
            
            error_log("===== updateCertificate END =====");

        } catch (Exception $e) {
            error_log("Exception in updateCertificate: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // ========== POLICY FUNCTIONS ==========

    public function getPolicyTypes() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';
            
            $query = "SELECT DISTINCT doc_type FROM " . $this->documentMasterTable . " 
                    WHERE type = 'policy' AND doc_type IS NOT NULL AND doc_type != '' AND status = 1";
            
            if (!empty($term)) {
                $query .= " AND doc_type LIKE :term";
            }
            
            $query .= " ORDER BY doc_type ASC";
            
            $params = array();
            if (!empty($term)) {
                $params[':term'] = '%' . $term . '%';
            }
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
            $types = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $types[] = array(
                        'value' => $row['doc_type'],
                        'label' => $row['doc_type']
                    );
                }
            }

            echo json_encode($types);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching policy types: " . $e->getMessage());
        }
    }

    // ✅ FIXED: getPolicies() - Always sort by create_date DESC
    public function getPolicies() {
        try {
            $draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
            $start = isset($_POST['start']) ? $_POST['start'] : 0;
            $length = isset($_POST['length']) ? $_POST['length'] : 10;
            $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

            $filterType = isset($_POST['type']) ? trim($_POST['type']) : '';
            $filterDateFrom = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
            $filterDateTo = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

            $params = [];

            $sql = "SELECT * FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $params[':type'] = 'policy';

            if (!empty($filterType)) {
                $sql .= " AND doc_type = :type_filter";
                $params[':type_filter'] = $filterType;
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            if (!empty($search)) {
                $sql .= " AND (doc_type LIKE :search OR remark LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $totalSql = "SELECT COUNT(*) as count FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $totalParams = [':type' => 'policy'];
            $totalRecords = DbManager::fetchPDOQueryData('spectra_db', $totalSql, $totalParams)['data'][0]['count'];

            $countSql = "SELECT COUNT(*) as count FROM (" . $sql . ") as counted";
            $filteredRecords = DbManager::fetchPDOQueryData('spectra_db', $countSql, $params)['data'][0]['count'];

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $sql .= " LIMIT :start, :length";
            $params[':start'] = (int)$start;
            $params[':length'] = (int)$length;

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            $response = [
                'draw' => (int)$draw,
                'recordsTotal' => (int)$totalRecords,
                'recordsFiltered' => (int)$filteredRecords,
                'data' => $result['data'] ?? []
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching policies: " . $e->getMessage());
        }
    }

    public function getAllPolicies() {
        try {
            $filterType = isset($_POST['type']) ? trim($_POST['type']) : '';
            $filterDateFrom = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
            $filterDateTo = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

            $params = [];

            $sql = "SELECT * FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $params[':type'] = 'policy';

            if (!empty($filterType)) {
                $sql .= " AND doc_type = :type_filter";
                $params[':type_filter'] = $filterType;
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            if (isset($result['data']) && !empty($result['data'])) {
                echo json_encode($result['data']);
            } else {
                echo json_encode([]);
            }
        } catch (Exception $e) {
            error_log("Error in getAllPolicies: " . $e->getMessage());
            echo json_encode([]);
        }
    }

    public function getPolicy() {
        try {
            $id = isset($_POST['id']) ? $_POST['id'] : null;

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Policy ID is required'
                ]);
                return;
            }

            $query = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1 AND type = 'policy'";
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, [':id' => $id]);

            if (isset($response['data']) && !empty($response['data'])) {
                $policy = $response['data'][0];
                
                $hasExistingFile = (!empty($policy['file_path']) && trim($policy['file_path']) !== '') 
                                && (!empty($policy['actual_file_name']) && trim($policy['actual_file_name']) !== '');
                
                $policy['hasExistingFile'] = $hasExistingFile;
                
                echo json_encode([
                    'success' => true,
                    'data' => $policy
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Policy not found'
                ]);
            }
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching policy: " . $e->getMessage());
        }
    }

    public function savePolicy() {
        try {
            error_log("===== savePolicy START =====");

            $type = isset($_POST['policy_type']) ? trim($_POST['policy_type']) : '';
            $issue_date = isset($_POST['policy_issue_date']) ? trim($_POST['policy_issue_date']) : '';
            $remark = isset($_POST['policy_remark']) ? trim($_POST['policy_remark']) : '';

            error_log("Data extracted - Type: $type, IssueDate: $issue_date");

            if (empty($type)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Policy Type is required'
                ]);
                return;
            }

            if (empty($issue_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Issue Date is required'
                ]);
                return;
            }

            $convertedIssueDate = !empty($issue_date) ? $this->convertDateFormatFlexible($issue_date) : null;

            if (!empty($issue_date) && !$convertedIssueDate) {
                error_log("Invalid issue date format: $issue_date");
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['policy_pdf_file']) && $_FILES['policy_pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['policy_pdf_file'], $type);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'PDF file is required'
                ]);
                return;
            }

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, doc_type, issue_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :doc_type, :issue_date, :remark, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => 'policy',
                ':doc_type' => $type,
                ':issue_date' => $convertedIssueDate,
                ':remark' => $remark,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ Policy saved successfully");
                echo json_encode([
                    'success' => true,
                    'message' => 'Policy saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ]);
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode([
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving policy. Please try again.'
                ]);
            }

            error_log("===== savePolicy END =====");

        } catch (Exception $e) {
            error_log("Exception in savePolicy: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePolicy() {
        try {
            error_log("===== updatePolicy START =====");
            
            $requiredFields = array('id', 'type', 'issue_date');
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    echo json_encode([
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                    ]);
                    return;
                }
            }

            $id = (int)$_POST['id'];
            
            // ✅ FETCH ORIGINAL DATA FOR SMART UPDATE
            $getOldQuery = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id AND type = 'policy'";
            $oldData = DbManager::fetchPDOQueryData('spectra_db', $getOldQuery, [':id' => $id]);

            if (empty($oldData['data'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Policy not found'
                ]);
                return;
            }

            $oldPolicy = $oldData['data'][0];

            $type = trim($_POST['type']);
            $issue_date = trim($_POST['issue_date']);
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

            error_log("Policy ID: " . $id . ", Type: " . $type . ", IssueDate: " . $issue_date);

            $convertedDate = $this->convertDateFormatFlexible($issue_date);
            if (!$convertedDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $hasFile = isset($_FILES['pdf_file']) && 
                      $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK &&
                      $_FILES['pdf_file']['size'] > 0;

            error_log("File Present: " . ($hasFile ? 'YES' : 'NO'));

            // ✅ TRACK CHANGED FIELDS
            $changedFields = [];

            if ($type !== $oldPolicy['doc_type']) {
                $changedFields['doc_type'] = $type;
            }
            if ($convertedDate !== $oldPolicy['issue_date']) {
                $changedFields['issue_date'] = $convertedDate;
            }
            if ($remark !== ($oldPolicy['remark'] ?? '')) {
                $changedFields['remark'] = $remark;
            }

            $newFilePath = null;
            $newActualFileName = null;

            if ($hasFile) {
                error_log("Processing policy file: " . $_FILES['pdf_file']['name']);
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $type);
                
                if (!$uploadResult['success']) {
                    error_log("Policy upload failed: " . $uploadResult['message']);
                    echo json_encode($uploadResult);
                    return;
                }
                
                $newFilePath = $uploadResult['file_path'];
                $newActualFileName = $uploadResult['actual_file_name'];
                error_log("Policy uploaded successfully");

                $archiveQuery = "INSERT INTO document_master_archive 
                                (dm_id, type, doc_type, issue_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                                VALUES 
                                (:dm_id, :type, :doc_type, :issue_date, :remark, :file_path, :actual_file_name, :status, :create_date, :update_date)";

                $archiveParams = [
                    ':dm_id' => $id,
                    ':type' => $oldPolicy['type'],
                    ':doc_type' => $oldPolicy['doc_type'],
                    ':issue_date' => $oldPolicy['issue_date'],
                    ':remark' => $oldPolicy['remark'],
                    ':file_path' => $oldPolicy['file_path'],
                    ':actual_file_name' => isset($oldPolicy['actual_file_name']) ? $oldPolicy['actual_file_name'] : '',
                    ':status' => $oldPolicy['status'],
                    ':create_date' => $oldPolicy['create_date'],
                    ':update_date' => $oldPolicy['update_date']
                ];

                $archiveResult = DbManager::fetchPDOQuery('spectra_db', $archiveQuery, $archiveParams);

                if (!$archiveResult) {
                    if ($newFilePath) {
                        $this->deleteFile($newFilePath);
                    }
                    error_log("Archive failed");
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error archiving old policy'
                    ]);
                    return;
                }

                error_log("Policy archived successfully");

                $changedFields['file_path'] = $newFilePath;
                $changedFields['actual_file_name'] = $newActualFileName;
            }

            // ✅ SMART UPDATE: Only update if there are changes
            if (empty($changedFields)) {
                echo json_encode([
                    'success' => true,
                    'message' => "No changes detected. Policy remains unchanged."
                ]);
                error_log("===== updatePolicy END (NO CHANGES) =====");
                return;
            }

            $updateQuery = "UPDATE " . $this->documentMasterTable . " SET ";
            $updateParams = [':id' => $id];
            
            $updateParts = [];
            foreach ($changedFields as $field => $value) {
                $updateParts[] = "$field = :$field";
                $updateParams[":$field"] = $value;
            }
            
            $updateParts[] = "update_date = NOW()";
            $updateQuery .= implode(', ', $updateParts) . " WHERE id = :id";

            $updateResult = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);

            if ($updateResult['status'] === 'success' || (isset($updateResult['data']) && $updateResult['data'] > 0)) {
                error_log("Policy updated successfully. Changed fields: " . count($changedFields));
                
                echo json_encode([
                    'success' => true,
                    'message' => "Policy updated successfully! " . count($changedFields) . " field(s) changed.",
                    'changedFieldsCount' => count($changedFields)
                ]);
            } else {
                if ($newFilePath) {
                    $this->deleteFile($newFilePath);
                }
                
                error_log("Database update failed");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating policy'
                ]);
            }
            
            error_log("===== updatePolicy END =====");

        } catch (Exception $e) {
            error_log("Exception in updatePolicy: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // ========== MANUAL FUNCTIONS ==========

    public function getManualTypes() {
        try {
            $term = isset($_POST['term']) ? $_POST['term'] : '';
            
            $query = "SELECT DISTINCT doc_type FROM " . $this->documentMasterTable . " 
                    WHERE type = 'manual' AND doc_type IS NOT NULL AND doc_type != '' AND status = 1";
            
            if (!empty($term)) {
                $query .= " AND doc_type LIKE :term";
            }
            
            $query .= " ORDER BY doc_type ASC";
            
            $params = array();
            if (!empty($term)) {
                $params[':term'] = '%' . $term . '%';
            }
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, $params);
            $types = array();

            if (isset($response['data']) && !empty($response['data'])) {
                foreach ($response['data'] as $row) {
                    $types[] = array(
                        'value' => $row['doc_type'],
                        'label' => $row['doc_type']
                    );
                }
            }

            echo json_encode($types);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching manual types: " . $e->getMessage());
        }
    }

    // ✅ FIXED: getManuals() - Always sort by create_date DESC
    public function getManuals() {
        try {
            $draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
            $start = isset($_POST['start']) ? $_POST['start'] : 0;
            $length = isset($_POST['length']) ? $_POST['length'] : 10;
            $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

            $filterManualType = isset($_POST['manual_type']) ? trim($_POST['manual_type']) : '';
            $filterDateFrom = isset($_POST['issue_date_from']) ? trim($_POST['issue_date_from']) : '';
            $filterDateTo = isset($_POST['issue_date_to']) ? trim($_POST['issue_date_to']) : '';

            $params = [];

            $sql = "SELECT 
                        id,
                        doc_type as manual_type,
                        version,
                        issue_date,
                        remark,
                        file_path,
                        actual_file_name,
                        create_date
                    FROM " . $this->documentMasterTable . " 
                    WHERE status = 1 AND type = :type";
            $params[':type'] = 'manual';

            if (!empty($filterManualType)) {
                $sql .= " AND doc_type = :manual_type";
                $params[':manual_type'] = $filterManualType;
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            if (!empty($search)) {
                $sql .= " AND (doc_type LIKE :search OR version LIKE :search OR remark LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $totalSql = "SELECT COUNT(*) as count FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $totalParams = [':type' => 'manual'];
            $totalRecords = DbManager::fetchPDOQueryData('spectra_db', $totalSql, $totalParams)['data'][0]['count'];

            $countSql = "SELECT COUNT(*) as count FROM (" . $sql . ") as counted";
            $filteredRecords = DbManager::fetchPDOQueryData('spectra_db', $countSql, $params)['data'][0]['count'];

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $sql .= " LIMIT :start, :length";
            $params[':start'] = (int)$start;
            $params[':length'] = (int)$length;

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            $response = [
                'draw' => (int)$draw,
                'recordsTotal' => (int)$totalRecords,
                'recordsFiltered' => (int)$filteredRecords,
                'data' => $result['data'] ?? []
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching manuals: " . $e->getMessage());
        }
    }

    public function getAllManuals() {
        try {
            $filterManualType = isset($_POST['manual_type']) ? trim($_POST['manual_type']) : '';
            $filterDateFrom = isset($_POST['issue_date_from']) ? trim($_POST['issue_date_from']) : '';
            $filterDateTo = isset($_POST['issue_date_to']) ? trim($_POST['issue_date_to']) : '';

            $params = [];

            $sql = "SELECT 
                        id,
                        doc_type as manual_type,
                        version,
                        issue_date,
                        remark,
                        file_path,
                        actual_file_name,
                        create_date
                    FROM " . $this->documentMasterTable . " 
                    WHERE status = 1 AND type = :type";
            $params[':type'] = 'manual';

            if (!empty($filterManualType)) {
                $sql .= " AND doc_type = :manual_type";
                $params[':manual_type'] = $filterManualType;
            }

            if (!empty($filterDateFrom) && !empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :date_from AND :date_to";
                $params[':date_from'] = $filterDateFrom;
                $params[':date_to'] = $filterDateTo;
            } elseif (!empty($filterDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :date_from";
                $params[':date_from'] = $filterDateFrom;
            } elseif (!empty($filterDateTo)) {
                $sql .= " AND DATE(issue_date) <= :date_to";
                $params[':date_to'] = $filterDateTo;
            }

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            if (isset($result['data']) && !empty($result['data'])) {
                echo json_encode($result['data']);
            } else {
                echo json_encode([]);
            }
        } catch (Exception $e) {
            error_log("Error in getAllManuals: " . $e->getMessage());
            echo json_encode([]);
        }
    }

    public function getManual() {
        try {
            $id = isset($_POST['id']) ? $_POST['id'] : null;

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Manual ID is required'
                ]);
                return;
            }

            $query = "SELECT 
                        id,
                        doc_type as manual_type,
                        version,
                        issue_date,
                        remark,
                        file_path,
                        actual_file_name,
                        status
                      FROM " . $this->documentMasterTable . " 
                      WHERE id = :id AND status = 1 AND type = 'manual'";
            
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, [':id' => $id]);

            if (isset($response['data']) && !empty($response['data'])) {
                $manual = $response['data'][0];
                
                $hasExistingFile = (!empty($manual['file_path']) && trim($manual['file_path']) !== '') 
                                && (!empty($manual['actual_file_name']) && trim($manual['actual_file_name']) !== '');
                
                $manual['hasExistingFile'] = $hasExistingFile;
                
                echo json_encode([
                    'success' => true,
                    'data' => $manual
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Manual not found'
                ]);
            }
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching manual: " . $e->getMessage());
        }
    }

    public function saveManual() {
        try {
            error_log("===== saveManual START =====");

            $manual_type = isset($_POST['manual_type']) ? trim($_POST['manual_type']) : '';
            $issue_date = isset($_POST['issue_date']) ? trim($_POST['issue_date']) : '';
            $version = isset($_POST['version']) ? trim($_POST['version']) : '';
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

            error_log("Data extracted - ManualType: $manual_type, Version: $version");

            if (empty($manual_type)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Manual Type is required'
                ]);
                return;
            }

            if (empty($version)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Version is required'
                ]);
                return;
            }

            if (empty($issue_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Issue Date is required'
                ]);
                return;
            }

            $convertedIssueDate = !empty($issue_date) ? $this->convertDateFormatFlexible($issue_date) : null;

            if (!empty($issue_date) && !$convertedIssueDate) {
                error_log("Invalid issue date format: $issue_date");
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $manual_type);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'PDF file is required'
                ]);
                return;
            }

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, doc_type, version, issue_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :doc_type, :version, :issue_date, :remark, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => 'manual',
                ':doc_type' => $manual_type,
                ':version' => $version,
                ':issue_date' => $convertedIssueDate,
                ':remark' => $remark,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ Manual saved successfully");
                echo json_encode([
                    'success' => true,
                    'message' => 'Manual saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ]);
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode([
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving manual. Please try again.'
                ]);
            }

            error_log("===== saveManual END =====");

        } catch (Exception $e) {
            error_log("Exception in saveManual: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updateManual() {
        try {
            error_log("===== updateManual START =====");
            
            $requiredFields = array('id', 'manual_type', 'manual_version', 'issue_date');
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    echo json_encode([
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                    ]);
                    return;
                }
            }

            $id = (int)$_POST['id'];
            
            // ✅ FETCH ORIGINAL DATA FOR SMART UPDATE
            $getOldQuery = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id AND type = 'manual'";
            $oldData = DbManager::fetchPDOQueryData('spectra_db', $getOldQuery, [':id' => $id]);

            if (empty($oldData['data'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Manual not found'
                ]);
                return;
            }

            $oldManual = $oldData['data'][0];

            $manual_type = trim($_POST['manual_type']);
            $version = trim($_POST['manual_version']);
            $issue_date = trim($_POST['issue_date']);
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

            error_log("Manual ID: " . $id . ", ManualType: " . $manual_type . ", Version: " . $version);

            $convertedDate = $this->convertDateFormatFlexible($issue_date);
            if (!$convertedDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $hasFile = isset($_FILES['pdf_file']) && 
                      $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK &&
                      $_FILES['pdf_file']['size'] > 0;

            error_log("File Present: " . ($hasFile ? 'YES' : 'NO'));

            // ✅ TRACK CHANGED FIELDS
            $changedFields = [];

            if ($manual_type !== $oldManual['doc_type']) {
                $changedFields['doc_type'] = $manual_type;
            }
            if ($version !== $oldManual['version']) {
                $changedFields['version'] = $version;
            }
            if ($convertedDate !== $oldManual['issue_date']) {
                $changedFields['issue_date'] = $convertedDate;
            }
            if ($remark !== ($oldManual['remark'] ?? '')) {
                $changedFields['remark'] = $remark;
            }

            $newFilePath = null;
            $newActualFileName = null;

            if ($hasFile) {
                error_log("Processing manual file: " . $_FILES['pdf_file']['name']);
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $manual_type);
                
                if (!$uploadResult['success']) {
                    error_log("Manual upload failed: " . $uploadResult['message']);
                    echo json_encode($uploadResult);
                    return;
                }
                
                $newFilePath = $uploadResult['file_path'];
                $newActualFileName = $uploadResult['actual_file_name'];
                error_log("Manual uploaded successfully");

                $archiveQuery = "INSERT INTO document_master_archive 
                                (dm_id, type, doc_type, version, issue_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                                VALUES 
                                (:dm_id, :type, :doc_type, :version, :issue_date, :remark, :file_path, :actual_file_name, :status, :create_date, :update_date)";

                $archiveParams = [
                    ':dm_id' => $id,
                    ':type' => $oldManual['type'],
                    ':doc_type' => $oldManual['doc_type'],
                    ':version' => $oldManual['version'],
                    ':issue_date' => $oldManual['issue_date'],
                    ':remark' => $oldManual['remark'],
                    ':file_path' => $oldManual['file_path'],
                    ':actual_file_name' => isset($oldManual['actual_file_name']) ? $oldManual['actual_file_name'] : '',
                    ':status' => $oldManual['status'],
                    ':create_date' => $oldManual['create_date'],
                    ':update_date' => $oldManual['update_date']
                ];

                $archiveResult = DbManager::fetchPDOQuery('spectra_db', $archiveQuery, $archiveParams);

                if (!$archiveResult) {
                    if ($newFilePath) {
                        $this->deleteFile($newFilePath);
                    }
                    error_log("Archive failed");
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error archiving old manual'
                    ]);
                    return;
                }

                error_log("Manual archived successfully");

                $changedFields['file_path'] = $newFilePath;
                $changedFields['actual_file_name'] = $newActualFileName;
            }

            // ✅ SMART UPDATE: Only update if there are changes
            if (empty($changedFields)) {
                echo json_encode([
                    'success' => true,
                    'message' => "No changes detected. Manual remains unchanged."
                ]);
                error_log("===== updateManual END (NO CHANGES) =====");
                return;
            }

            $updateQuery = "UPDATE " . $this->documentMasterTable . " SET ";
            $updateParams = [':id' => $id];
            
            $updateParts = [];
            foreach ($changedFields as $field => $value) {
                $updateParts[] = "$field = :$field";
                $updateParams[":$field"] = $value;
            }
            
            $updateParts[] = "update_date = NOW()";
            $updateQuery .= implode(', ', $updateParts) . " WHERE id = :id";

            $updateResult = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);

            if ($updateResult['status'] === 'success' || (isset($updateResult['data']) && $updateResult['data'] > 0)) {
                error_log("Manual updated successfully. Changed fields: " . count($changedFields));
                
                echo json_encode([
                    'success' => true,
                    'message' => "Manual updated successfully! " . count($changedFields) . " field(s) changed.",
                    'changedFieldsCount' => count($changedFields)
                ]);
            } else {
                if ($newFilePath) {
                    $this->deleteFile($newFilePath);
                }
                
                error_log("Database update failed");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating manual'
                ]);
            }
            
            error_log("===== updateManual END =====");

        } catch (Exception $e) {
            error_log("Exception in updateManual: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function viewManualFile() {
        try {
            error_log("========== VIEW MANUAL FILE START ==========");
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', 1);
            }
            ini_set('zlib.output_compression', 'Off');
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $manualId = isset($input['manual_id']) ? (int)$input['manual_id'] : null;

            error_log("[VIEW] ManualID: " . ($manualId ? $manualId : 'NULL'));

            if (!$manualId) {
                error_log("[VIEW] ERROR: Missing manual ID");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Manual ID required']);
                exit;
            }

            $userModules = SharedManager::getUser()["Modules"];
            $hasViewRights = in_array(22, $userModules);
            $hasAdminRights = in_array(23, $userModules);

            if (!$hasViewRights && !$hasAdminRights) {
                error_log("[VIEW] ERROR: Access denied");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            $docQuery = "SELECT file_path, actual_file_name FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1 AND type = 'manual'";
            $docResult = DbManager::fetchPDOQueryData('spectra_db', $docQuery, [':id' => $manualId]);
            
            if (empty($docResult['data'])) {
                error_log("[VIEW] ERROR: Manual not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Manual not found']);
                exit;
            }

            $filePath = $docResult['data'][0]['file_path'];
            $actualFileName = $docResult['data'][0]['actual_file_name'];

            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            $realPath = realpath($fullPath);
            $uploadRealPath = realpath($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir);

            error_log("[VIEW] Full path: " . $fullPath);
            error_log("[VIEW] Real path: " . ($realPath ? $realPath : 'NULL'));

            if ($realPath === false || strpos($realPath, $uploadRealPath) !== 0) {
                error_log("[VIEW] ERROR: Security check failed");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            if (!file_exists($realPath) || !is_file($realPath)) {
                error_log("[VIEW] ERROR: File not found at " . $realPath);
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File not found']);
                exit;
            }

            $fileExtension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            
            if ($fileExtension !== 'pdf') {
                error_log("[VIEW] ERROR: Only PDF files are supported");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Only PDF files are supported']);
                exit;
            }

            $mimeType = 'application/pdf';

            $fileSize = filesize($realPath);
            
            if ($fileSize === false || $fileSize <= 0) {
                error_log("[VIEW] ERROR: Cannot determine file size");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot read file']);
                exit;
            }

            error_log("[VIEW] File: " . $actualFileName . ", Size: " . $fileSize . " bytes");

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType, true);
            header('Content-Disposition: inline; filename="' . basename($actualFileName) . '"', true);
            header('Content-Length: ' . $fileSize, true);
            header('Cache-Control: no-cache, no-store, must-revalidate', true);
            header('Pragma: no-cache', true);
            header('Expires: 0', true);
            header('Accept-Ranges: bytes', true);

            flush();

            $chunkSize = 1024 * 1024;
            $handle = fopen($realPath, 'rb');

            if (!$handle) {
                error_log("[VIEW] ERROR: Cannot open file");
                http_response_code(500);
                exit;
            }

            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false) {
                    error_log("[VIEW] ERROR: Error reading file chunk");
                    break;
                }
                echo $chunk;
                flush();
                
                if (connection_aborted()) {
                    error_log("[VIEW] Connection aborted by client");
                    break;
                }
            }

            fclose($handle);
            error_log("[VIEW] ✓ File sent for viewing - " . $actualFileName);
            error_log("========== VIEW MANUAL FILE END ==========");
            exit;

        } catch (Exception $e) {
            error_log("[VIEW] EXCEPTION: " . $e->getMessage());
            error_log("[VIEW] Stack trace: " . $e->getTraceAsString());
            
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }
    }

    public function downloadManualPdf() {
        try {
            error_log("========== DOWNLOAD MANUAL PDF START ==========");
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', 1);
            }
            ini_set('zlib.output_compression', 'Off');
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $manualId = isset($input['manual_id']) ? (int)$input['manual_id'] : null;

            error_log("[DOWNLOAD] ManualID: " . ($manualId ? $manualId : 'NULL'));

            if (!$manualId) {
                error_log("[DOWNLOAD] ERROR: Missing manual ID");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Manual ID required']);
                exit;
            }

            $userModules = SharedManager::getUser()["Modules"];
            $hasViewRights = in_array(22, $userModules);
            $hasAdminRights = in_array(23, $userModules);

            error_log("[DOWNLOAD] User Permissions - ViewRights: " . ($hasViewRights ? 'YES' : 'NO') . ", AdminRights: " . ($hasAdminRights ? 'YES' : 'NO'));

            if (!$hasViewRights && !$hasAdminRights) {
                error_log("[DOWNLOAD] ERROR: Access denied");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            $docQuery = "SELECT file_path, actual_file_name FROM " . $this->documentMasterTable . " WHERE id = :id AND status = 1 AND type = 'manual'";
            $docResult = DbManager::fetchPDOQueryData('spectra_db', $docQuery, [':id' => $manualId]);
            
            if (empty($docResult['data'])) {
                error_log("[DOWNLOAD] ERROR: Manual not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Manual not found']);
                exit;
            }

            $filePath = $docResult['data'][0]['file_path'];
            $actualFileName = $docResult['data'][0]['actual_file_name'];

            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            $realPath = realpath($fullPath);
            $uploadRealPath = realpath($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir);

            error_log("[DOWNLOAD] Full path: " . $fullPath);
            error_log("[DOWNLOAD] Real path: " . ($realPath ? $realPath : 'NULL'));

            if ($realPath === false || strpos($realPath, $uploadRealPath) !== 0) {
                error_log("[DOWNLOAD] ERROR: Security check failed");
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            if (!file_exists($realPath) || !is_file($realPath)) {
                error_log("[DOWNLOAD] ERROR: File not found");
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'File not found']);
                exit;
            }

            $fileExtension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

            error_log("[DOWNLOAD] File extension: " . $fileExtension);

            if ($fileExtension !== 'pdf') {
                error_log("[DOWNLOAD] ERROR: Not a PDF file");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Only PDF files are supported']);
                exit;
            }

            if (!$this->validatePdfFile($realPath)) {
                error_log("[DOWNLOAD] ERROR: Invalid PDF file");
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid or corrupted PDF file']);
                exit;
            }

            error_log("[DOWNLOAD] ✓ PDF validation passed");

            $pdfData = file_get_contents($realPath);
            if (!$pdfData) {
                error_log("[DOWNLOAD] ERROR: Cannot read PDF file");
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot read file']);
                exit;
            }

            $pdfSize = strlen($pdfData);
            error_log("[DOWNLOAD] PDF size: " . $pdfSize . " bytes");

            $applyWatermark = !$hasAdminRights;

            error_log("[DOWNLOAD] Apply Watermark: " . ($applyWatermark ? 'YES' : 'NO (Admin User)'));

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf', true);
            header('Content-Disposition: attachment; filename="' . basename($actualFileName) . '"', true);
            header('Content-Length: ' . $pdfSize, true);
            header('Cache-Control: no-cache, no-store, must-revalidate', true);
            header('Pragma: no-cache', true);
            header('Expires: 0', true);
            header('Connection: close', true);

            header('X-Has-Admin-Rights: ' . ($hasAdminRights ? 'true' : 'false'), true);
            header('X-Apply-Watermark: ' . ($applyWatermark ? 'true' : 'false'), true);

            error_log("[DOWNLOAD] ✓ Headers sent successfully");

            echo $pdfData;
            
            error_log("[DOWNLOAD] ✓ Original PDF sent to client");
            error_log("[DOWNLOAD] Client will " . ($applyWatermark ? "apply CONTROLLED COPY watermark" : "NOT apply watermark (Admin)"));
            error_log("========== DOWNLOAD MANUAL PDF END ==========");
            exit;

        } catch (Exception $e) {
            error_log("[DOWNLOAD] EXCEPTION: " . $e->getMessage());
            error_log("[DOWNLOAD] Stack trace: " . $e->getTraceAsString());
            
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }
    }

    // ========== FAC FUNCTIONS ==========

    // ✅ FIXED: getFacs() - Always sort by create_date DESC
    public function getFacs() {
        try {
            $draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
            $start = isset($_POST['start']) ? $_POST['start'] : 0;
            $length = isset($_POST['length']) ? $_POST['length'] : 10;
            $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

            $filterDepartment = isset($_POST['department']) ? trim($_POST['department']) : '';
            $filterCertificateNo = isset($_POST['certificate_no']) ? trim($_POST['certificate_no']) : '';
            $filterIssueDateFrom = isset($_POST['issue_date_from']) ? trim($_POST['issue_date_from']) : '';
            $filterIssueDateTo = isset($_POST['issue_date_to']) ? trim($_POST['issue_date_to']) : '';
            $filterExpiryDateFrom = isset($_POST['expiry_date_from']) ? trim($_POST['expiry_date_from']) : '';
            $filterExpiryDateTo = isset($_POST['expiry_date_to']) ? trim($_POST['expiry_date_to']) : '';

            $params = [];

            $sql = "SELECT id, department, issue_date, next_review_date as expiry_date, remark, create_date 
                    FROM " . $this->documentMasterTable . " 
                    WHERE status = 1 AND type = :type";
            $params[':type'] = 'fac';

            if (!empty($filterDepartment)) {
                $sql .= " AND department = :department";
                $params[':department'] = $filterDepartment;
            }

            if (!empty($filterIssueDateFrom) && !empty($filterIssueDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :issue_date_from AND :issue_date_to";
                $params[':issue_date_from'] = $filterIssueDateFrom;
                $params[':issue_date_to'] = $filterIssueDateTo;
            } elseif (!empty($filterIssueDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :issue_date_from";
                $params[':issue_date_from'] = $filterIssueDateFrom;
            } elseif (!empty($filterIssueDateTo)) {
                $sql .= " AND DATE(issue_date) <= :issue_date_to";
                $params[':issue_date_to'] = $filterIssueDateTo;
            }

            if (!empty($filterExpiryDateFrom) && !empty($filterExpiryDateTo)) {
                $sql .= " AND DATE(next_review_date) BETWEEN :expiry_date_from AND :expiry_date_to";
                $params[':expiry_date_from'] = $filterExpiryDateFrom;
                $params[':expiry_date_to'] = $filterExpiryDateTo;
            } elseif (!empty($filterExpiryDateFrom)) {
                $sql .= " AND DATE(next_review_date) >= :expiry_date_from";
                $params[':expiry_date_from'] = $filterExpiryDateFrom;
            } elseif (!empty($filterExpiryDateTo)) {
                $sql .= " AND DATE(next_review_date) <= :expiry_date_to";
                $params[':expiry_date_to'] = $filterExpiryDateTo;
            }

            if (!empty($search)) {
                $sql .= " AND (department LIKE :search OR remark LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $totalSql = "SELECT COUNT(*) as count FROM " . $this->documentMasterTable . " WHERE status = 1 AND type = :type";
            $totalParams = [':type' => 'fac'];
            $totalRecords = DbManager::fetchPDOQueryData('spectra_db', $totalSql, $totalParams)['data'][0]['count'];

            $countSql = "SELECT COUNT(*) as count FROM (" . $sql . ") as counted";
            $filteredRecords = DbManager::fetchPDOQueryData('spectra_db', $countSql, $params)['data'][0]['count'];

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $sql .= " LIMIT :start, :length";
            $params[':start'] = (int)$start;
            $params[':length'] = (int)$length;

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            $response = [
                'draw' => (int)$draw,
                'recordsTotal' => (int)$totalRecords,
                'recordsFiltered' => (int)$filteredRecords,
                'data' => $result['data'] ?? []
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching FACs: " . $e->getMessage());
        }
    }

    public function getAllFacs() {
        try {
            $filterDepartment = isset($_POST['department']) ? trim($_POST['department']) : '';
            $filterIssueDateFrom = isset($_POST['issue_date_from']) ? trim($_POST['issue_date_from']) : '';
            $filterIssueDateTo = isset($_POST['issue_date_to']) ? trim($_POST['issue_date_to']) : '';
            $filterExpiryDateFrom = isset($_POST['expiry_date_from']) ? trim($_POST['expiry_date_from']) : '';
            $filterExpiryDateTo = isset($_POST['expiry_date_to']) ? trim($_POST['expiry_date_to']) : '';

            $params = [];

            $sql = "SELECT id, department, issue_date, next_review_date as expiry_date, remark, create_date 
                    FROM " . $this->documentMasterTable . " 
                    WHERE status = 1 AND type = :type";
            $params[':type'] = 'fac';

            if (!empty($filterDepartment)) {
                $sql .= " AND department = :department";
                $params[':department'] = $filterDepartment;
            }

            if (!empty($filterIssueDateFrom) && !empty($filterIssueDateTo)) {
                $sql .= " AND DATE(issue_date) BETWEEN :issue_date_from AND :issue_date_to";
                $params[':issue_date_from'] = $filterIssueDateFrom;
                $params[':issue_date_to'] = $filterIssueDateTo;
            } elseif (!empty($filterIssueDateFrom)) {
                $sql .= " AND DATE(issue_date) >= :issue_date_from";
                $params[':issue_date_from'] = $filterIssueDateFrom;
            } elseif (!empty($filterIssueDateTo)) {
                $sql .= " AND DATE(issue_date) <= :issue_date_to";
                $params[':issue_date_to'] = $filterIssueDateTo;
            }

            if (!empty($filterExpiryDateFrom) && !empty($filterExpiryDateTo)) {
                $sql .= " AND DATE(next_review_date) BETWEEN :expiry_date_from AND :expiry_date_to";
                $params[':expiry_date_from'] = $filterExpiryDateFrom;
                $params[':expiry_date_to'] = $filterExpiryDateTo;
            } elseif (!empty($filterExpiryDateFrom)) {
                $sql .= " AND DATE(next_review_date) >= :expiry_date_from";
                $params[':expiry_date_from'] = $filterExpiryDateFrom;
            } elseif (!empty($filterExpiryDateTo)) {
                $sql .= " AND DATE(next_review_date) <= :expiry_date_to";
                $params[':expiry_date_to'] = $filterExpiryDateTo;
            }

            // ✅ ALWAYS SORT BY CREATE_DATE DESC (NEWEST FIRST)
            $sql .= " ORDER BY create_date DESC";

            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

            if (isset($result['data']) && !empty($result['data'])) {
                echo json_encode($result['data']);
            } else {
                echo json_encode([]);
            }
        } catch (Exception $e) {
            error_log("Error in getAllFacs: " . $e->getMessage());
            echo json_encode([]);
        }
    }

    public function getFac() {
        try {
            $id = isset($_POST['id']) ? $_POST['id'] : null;

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'FAC ID is required'
                ]);
                return;
            }

            $query = "SELECT id, department, issue_date, next_review_date as expiry_date, remark, file_path, actual_file_name 
                      FROM " . $this->documentMasterTable . " 
                      WHERE id = :id AND status = 1 AND type = 'fac'";
            $response = DbManager::fetchPDOQueryData('spectra_db', $query, [':id' => $id]);

            if (isset($response['data']) && !empty($response['data'])) {
                $fac = $response['data'][0];
                
                $hasExistingFile = (!empty($fac['file_path']) && trim($fac['file_path']) !== '') 
                                && (!empty($fac['actual_file_name']) && trim($fac['actual_file_name']) !== '');
                
                $fac['hasExistingFile'] = $hasExistingFile;
                
                echo json_encode([
                    'success' => true,
                    'data' => $fac
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'FAC not found'
                ]);
            }
        } catch (Exception $e) {
            returnHttpResponse(500, "Error fetching FAC: " . $e->getMessage());
        }
    }

    public function saveFac() {
        try {
            error_log("===== saveFac START =====");

            $department = isset($_POST['department']) ? trim($_POST['department']) : '';
            $issue_date = isset($_POST['issue_date']) ? trim($_POST['issue_date']) : '';
            $expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : '';
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

            error_log("Data extracted - Department: $department");

            if (empty($department)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Department is required'
                ]);
                return;
            }

            if (empty($issue_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Issue Date is required'
                ]);
                return;
            }

            if (empty($expiry_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Expiry Date is required'
                ]);
                return;
            }

            $convertedIssueDate = !empty($issue_date) ? $this->convertDateFormatFlexible($issue_date) : null;
            $convertedExpiryDate = !empty($expiry_date) ? $this->convertDateFormatFlexible($expiry_date) : null;

            if (!empty($issue_date) && !$convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            if (!empty($expiry_date) && !$convertedExpiryDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid expiry date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            if ($convertedExpiryDate && $convertedIssueDate && $convertedExpiryDate <= $convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Expiry date must be after issue date"
                ]);
                return;
            }

            $filePath = null;
            $actualFileName = null;
            
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $department);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    return;
                }
                $filePath = $uploadResult['file_path'];
                $actualFileName = $uploadResult['actual_file_name'];
                error_log("File uploaded: $actualFileName");
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'PDF file is required'
                ]);
                return;
            }

            $insertQuery = "INSERT INTO " . $this->documentMasterTable . " 
                            (type, department, issue_date, next_review_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                            VALUES 
                            (:type, :department, :issue_date, :next_review_date, :remark, :file_path, :actual_file_name, :status, NOW(), NOW())";

            $insertParams = [
                ':type' => 'fac',
                ':department' => $department,
                ':issue_date' => $convertedIssueDate,
                ':next_review_date' => $convertedExpiryDate,
                ':remark' => $remark,
                ':file_path' => $filePath,
                ':actual_file_name' => $actualFileName,
                ':status' => 1
            ];

            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $insertParams);

            if ($insertResponse['status'] === 'success' || (isset($insertResponse['data']) && $insertResponse['data'] > 0)) {
                error_log("✓ FAC saved successfully");
                echo json_encode([
                    'success' => true,
                    'message' => 'FAC saved successfully!',
                    'id' => isset($insertResponse['data']) ? $insertResponse['data'] : null
                ]);
            } else {
                if ($filePath) {
                    $this->deleteFile($filePath);
                }
                error_log("✗ Insert failed: " . json_encode($insertResponse));
                echo json_encode([
                    'success' => false,
                    'message' => isset($insertResponse['message']) ? $insertResponse['message'] : 'Error saving FAC. Please try again.'
                ]);
            }

            error_log("===== saveFac END =====");

        } catch (Exception $e) {
            error_log("Exception in saveFac: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updateFac() {
        try {
            error_log("===== updateFac START =====");
            
            $requiredFields = array('id', 'department', 'issue_date', 'expiry_date');
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    echo json_encode([
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                    ]);
                    return;
                }
            }

            $id = (int)$_POST['id'];
            
            // ✅ FETCH ORIGINAL DATA FOR SMART UPDATE
            $getOldQuery = "SELECT * FROM " . $this->documentMasterTable . " WHERE id = :id AND type = 'fac'";
            $oldData = DbManager::fetchPDOQueryData('spectra_db', $getOldQuery, [':id' => $id]);

            if (empty($oldData['data'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'FAC not found'
                ]);
                return;
            }

            $oldFac = $oldData['data'][0];

            $department = trim($_POST['department']);
            $issue_date = trim($_POST['issue_date']);
            $expiry_date = trim($_POST['expiry_date']);
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

            error_log("FAC ID: " . $id . ", Department: " . $department);

            $convertedIssueDate = $this->convertDateFormatFlexible($issue_date);
            if (!$convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid issue date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            $convertedExpiryDate = $this->convertDateFormatFlexible($expiry_date);
            if (!$convertedExpiryDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Invalid expiry date format. Please use DD/MM/YYYY format."
                ]);
                return;
            }

            if ($convertedExpiryDate <= $convertedIssueDate) {
                echo json_encode([
                    'success' => false,
                    'message' => "Expiry date must be after issue date"
                ]);
                return;
            }

            $hasFile = isset($_FILES['pdf_file']) && 
                      $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK &&
                      $_FILES['pdf_file']['size'] > 0;

            error_log("File Present: " . ($hasFile ? 'YES' : 'NO'));

            // ✅ TRACK CHANGED FIELDS
            $changedFields = [];

            if ($department !== $oldFac['department']) {
                $changedFields['department'] = $department;
            }
            if ($convertedIssueDate !== $oldFac['issue_date']) {
                $changedFields['issue_date'] = $convertedIssueDate;
            }
            if ($convertedExpiryDate !== $oldFac['next_review_date']) {
                $changedFields['next_review_date'] = $convertedExpiryDate;
            }
            if ($remark !== ($oldFac['remark'] ?? '')) {
                $changedFields['remark'] = $remark;
            }

            $newFilePath = null;
            $newActualFileName = null;

            if ($hasFile) {
                error_log("Processing FAC file: " . $_FILES['pdf_file']['name']);
                $uploadResult = $this->handleFileUpload($_FILES['pdf_file'], $department);
                
                if (!$uploadResult['success']) {
                    error_log("FAC upload failed: " . $uploadResult['message']);
                    echo json_encode($uploadResult);
                    return;
                }
                
                $newFilePath = $uploadResult['file_path'];
                $newActualFileName = $uploadResult['actual_file_name'];
                error_log("FAC uploaded successfully");

                $archiveQuery = "INSERT INTO document_master_archive 
                                (dm_id, type, department, issue_date, next_review_date, remark, file_path, actual_file_name, status, create_date, update_date) 
                                VALUES 
                                (:dm_id, :type, :department, :issue_date, :next_review_date, :remark, :file_path, :actual_file_name, :status, :create_date, :update_date)";

                $archiveParams = [
                    ':dm_id' => $id,
                    ':type' => $oldFac['type'],
                    ':department' => $oldFac['department'],
                    ':issue_date' => $oldFac['issue_date'],
                    ':next_review_date' => $oldFac['next_review_date'],
                    ':remark' => $oldFac['remark'],
                    ':file_path' => $oldFac['file_path'],
                    ':actual_file_name' => isset($oldFac['actual_file_name']) ? $oldFac['actual_file_name'] : '',
                    ':status' => $oldFac['status'],
                    ':create_date' => $oldFac['create_date'],
                    ':update_date' => $oldFac['update_date']
                ];

                $archiveResult = DbManager::fetchPDOQuery('spectra_db', $archiveQuery, $archiveParams);

                if (!$archiveResult) {
                    if ($newFilePath) {
                        $this->deleteFile($newFilePath);
                    }
                    error_log("Archive failed");
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error archiving old FAC'
                    ]);
                    return;
                }

                error_log("FAC archived successfully");

                $changedFields['file_path'] = $newFilePath;
                $changedFields['actual_file_name'] = $newActualFileName;
            }

            // ✅ SMART UPDATE: Only update if there are changes
            if (empty($changedFields)) {
                echo json_encode([
                    'success' => true,
                    'message' => "No changes detected. FAC remains unchanged."
                ]);
                error_log("===== updateFac END (NO CHANGES) =====");
                return;
            }

            $updateQuery = "UPDATE " . $this->documentMasterTable . " SET ";
            $updateParams = [':id' => $id];
            
            $updateParts = [];
            foreach ($changedFields as $field => $value) {
                $updateParts[] = "$field = :$field";
                $updateParams[":$field"] = $value;
            }
            
            $updateParts[] = "update_date = NOW()";
            $updateQuery .= implode(', ', $updateParts) . " WHERE id = :id";

            $updateResult = DbManager::fetchPDOQuery('spectra_db', $updateQuery, $updateParams);

            if ($updateResult['status'] === 'success' || (isset($updateResult['data']) && $updateResult['data'] > 0)) {
                error_log("FAC updated successfully. Changed fields: " . count($changedFields));
                
                echo json_encode([
                    'success' => true,
                    'message' => "FAC updated successfully! " . count($changedFields) . " field(s) changed.",
                    'changedFieldsCount' => count($changedFields)
                ]);
            } else {
                if ($newFilePath) {
                    $this->deleteFile($newFilePath);
                }
                
                error_log("Database update failed");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating FAC'
                ]);
            }
            
            error_log("===== updateFac END =====");

        } catch (Exception $e) {
            error_log("Exception in updateFac: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // ========== HELPER FUNCTIONS ==========

    public function handleFileUpload($file, $document_number) {
        try {
            error_log("=== handleFileUpload START ===");
            
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                error_log("Upload error: " . $this->getUploadError($file['error']));
                return [
                    'success' => false,
                    'message' => 'File upload error: ' . $this->getUploadError($file['error'])
                ];
            }

            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            error_log("File Upload - Name: " . $file['name'] . ", Extension: " . $fileExtension . ", Size: " . $file['size']);

            $allowedExtensions = ['pdf', 'xlsx', 'xls', 'ppt', 'pptx', 'doc', 'docx'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                error_log("Invalid extension: " . $fileExtension);
                return [
                    'success' => false,
                    'message' => 'Only PDF (.pdf), Excel (.xlsx, .xls), PowerPoint (.ppt, .pptx), and Word (.doc, .docx) files are allowed'
                ];
            }

            if ($file['size'] > $this->maxFileSize) {
                error_log("File size exceeds limit: " . $file['size']);
                return [
                    'success' => false,
                    'message' => 'File size must be less than 50MB'
                ];
            }

            if ($fileExtension === 'pdf') {
                if (!$this->validatePdfFile($file['tmp_name'])) {
                    error_log("Invalid PDF file detected");
                    return [
                        'success' => false,
                        'message' => 'Invalid PDF file. File appears to be corrupted.'
                    ];
                }
            } 
            elseif (in_array($fileExtension, ['xlsx', 'xls', 'ppt', 'pptx', 'doc', 'docx'])) {
                if (!$this->validateOfficeFile($file['tmp_name'], $fileExtension)) {
                    error_log("Invalid Office file detected for extension: " . $fileExtension);
                    return [
                        'success' => false,
                        'message' => 'Invalid ' . strtoupper($fileExtension) . ' file. File appears to be corrupted.'
                    ];
                }
            }

            $timestamp = time();
            $sanitizedDocNumber = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document_number);
            $newFileName = $sanitizedDocNumber . '_' . $timestamp . '.' . $fileExtension;
            
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . $this->uploadDir;
            $filePath = $uploadDir . $newFileName;

            error_log("Upload Path: " . $filePath);

            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("Failed to create upload directory: " . $uploadDir);
                    return [
                        'success' => false,
                        'message' => 'Failed to create upload directory'
                    ];
                }
            }

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                error_log("Failed to move uploaded file to: " . $filePath);
                return [
                    'success' => false,
                    'message' => 'Failed to save file. Please try again.'
                ];
            }

            error_log("File uploaded successfully: " . $filePath);
            error_log("=== handleFileUpload END (SUCCESS) ===");

            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);

            return [
                'success' => true,
                'file_path' => $relativePath,
                'actual_file_name' => $file['name'],
                'file_type' => $fileExtension,
                'message' => 'File uploaded successfully'
            ];

        } catch (Exception $e) {
            error_log("Error in handleFileUpload: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error uploading file: ' . $e->getMessage()
            ];
        }
    }

    private function validateOfficeFile($filePath, $extension) {
        try {
            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                error_log("Cannot open file for validation: " . $filePath);
                return false;
            }

            $header = fread($handle, 8);
            fclose($handle);

            if ($header === false || strlen($header) < 4) {
                error_log("Cannot read file header for extension: " . $extension);
                return false;
            }

            $hexHeader = bin2hex($header);

            if (in_array($extension, ['docx', 'xlsx', 'pptx'])) {
                if (strtolower(substr($hexHeader, 0, 4)) === '504b') {
                    error_log("✓ Modern Office file validated for extension: " . $extension);
                    return true;
                }
            }

            if (in_array($extension, ['doc', 'xls', 'ppt'])) {
                if (strtolower(substr($hexHeader, 0, 4)) === 'd0cf') {
                    error_log("✓ OLE Compound Document file validated for extension: " . $extension);
                    return true;
                }
            }

            error_log("✗ Invalid file magic bytes for extension: " . $extension);
            return false;

        } catch (Exception $e) {
            error_log("Office file validation error: " . $e->getMessage());
            return false;
        }
    }

    private function validatePdfFile($filePath) {
        try {
            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                error_log("Cannot open PDF file for validation: " . $filePath);
                return false;
            }

            $header = fread($handle, 4);
            fclose($handle);

            if ($header === '%PDF') {
                error_log("✓ PDF file validated");
                return true;
            }

            error_log("✗ Invalid PDF header");
            return false;
        } catch (Exception $e) {
            error_log("PDF validation error: " . $e->getMessage());
            return false;
        }
    }

    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    private function deleteFile($filePath) {
        try {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            
            $realPath = realpath($fullPath);
            $uploadRealPath = realpath($_SERVER['DOCUMENT_ROOT'] . $this->uploadDir);
            
            if ($realPath === false || strpos($realPath, $uploadRealPath) !== 0) {
                error_log("Security: Attempted to delete file outside upload directory");
                return false;
            }
            
            if (file_exists($realPath) && is_file($realPath)) {
                if (!is_writable($realPath)) {
                    @chmod($realPath, 0666);
                }
                return unlink($realPath);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error deleting file: " . $e->getMessage());
            return false;
        }
    }

    private function addDepartmentIfNew($departmentName) {
        try {
            $checkQuery = "SELECT id FROM " . $this->departmentTable . " WHERE department_name = :name";
            $checkResponse = DbManager::fetchPDOQueryData('spectra_db', $checkQuery, [
                ':name' => $departmentName
            ]);

            if (isset($checkResponse['data']) && !empty($checkResponse['data'])) {
                return true;
            }

            $insertQuery = "INSERT INTO " . $this->departmentTable . " (department_name, create_date) VALUES (:name, NOW())";
            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, [
                ':name' => $departmentName
            ]);

            return $insertResponse['status'] === 'success';
        } catch (Exception $e) {
            error_log("Error adding department: " . $e->getMessage());
            return false;
        }
    }

    private function addDocTypeIfNew($docTypeName) {
        try {
            $checkQuery = "SELECT id FROM " . $this->docTypeTable . " WHERE doc_type = :name";
            $checkResponse = DbManager::fetchPDOQueryData('spectra_db', $checkQuery, [
                ':name' => $docTypeName
            ]);

            if (isset($checkResponse['data']) && !empty($checkResponse['data'])) {
                return true;
            }

            $insertQuery = "INSERT INTO " . $this->docTypeTable . " (doc_type, create_date) VALUES (:name, NOW())";
            $insertResponse = DbManager::fetchPDOQuery('spectra_db', $insertQuery, [
                ':name' => $docTypeName
            ]);

            return $insertResponse['status'] === 'success';
        } catch (Exception $e) {
            error_log("Error adding doc type: " . $e->getMessage());
            return false;
        }
    }

    private function convertDateFormatFlexible($date) {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        return false;
    }
}

?>