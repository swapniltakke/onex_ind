<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class ChecklistController extends BaseController {

    public function getChecklistItem() {
        $id = $_GET['id'];

        try {
            $query = "
                SELECT ci.*, cc.name as category_name
                FROM checklist_items ci
                LEFT JOIN checklist_categories cc ON ci.category_id = cc.id
                WHERE ci.id = :id
            ";

            $item = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'][0];

            $productQuery = "
                SELECT p.id as product_id, p.product_type
                FROM checklist_product_match cpm
                INNER JOIN products p ON cpm.product_type_id = p.id
                WHERE cpm.checklist_item_id = :id
            ";

            $products = DbManager::fetchPDOQueryData('dto_configurator', $productQuery, [':id' => $id])['data'];

            $item['products'] = $products;

            echo json_encode($item);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to load checklist item']);
            exit();
        }
    }


    public function getChecklistCategories() {
        $query = "SELECT id, name FROM checklist_categories ORDER BY name ASC";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'];

        echo json_encode($data);
        exit();
    }

    public function getChecklistProducts() {
        $query = "SELECT id, product_type FROM products WHERE id <= 7 ORDER BY id";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'];

        echo json_encode($data);
        exit();
    }

    public function getChecklistItems(){
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Checklist Items for Index Page");
        Journals::saveJournal("PROCESSING | Get Checklist Items for Index Page", PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_PROCESSING, '', "Checklist Index");

        $query = "SELECT 
                    checklist_item_id,
                    checklist_detail,
                    checklist_detail_html,
                    category_id,
                    category_name,
                    product_id,
                    product_type,
                    image_file_name
                  FROM view_checklist 
                  ORDER BY checklist_item_id";

        $rawData = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'] ?? [];

        // Group data by checklist_item_id
        $groupedData = [];

        foreach ($rawData as $row) {
            $itemId = $row['checklist_item_id'];

            // If this checklist item hasn't been added yet
            if (!isset($groupedData[$itemId])) {
                $groupedData[$itemId] = [
                    'checklist_item_id' => $row['checklist_item_id'],
                    'checklist_detail' => $row['checklist_detail'],
                    'checklist_detail_html' => $row['checklist_detail_html'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'image_file_name' => $row['image_file_name'],
                    'products' => []
                ];
            }

            // Add product to the products array (only if product_id exists)
            if (!empty($row['product_id'])) {
                $groupedData[$itemId]['products'][] = [
                    'product_id' => $row['product_id'],
                    'product_type' => $row['product_type']
                ];
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Get Checklist Items for Index Page Successfully Returned.");
        Journals::saveJournal("RETURNED | Get Checklist Items for Index Page Successfully Returned.", PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_VIEWED, '', "Checklist Index");

        echo json_encode(array_values($groupedData));
        exit();
    }

    public function getProjectChecklistItems() {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        $projectId = $this->getDtoConfiguratorProjectId($projectNo, $nachbauNo);
        $productId = $this->getProductIdOfProject($projectNo);

        // Step 1: Get all checklist items for this product type
        $checklistItemsQuery = "
            SELECT DISTINCT 
                checklist_item_id,
                checklist_detail,
                checklist_detail_html,
                category_id,
                category_name,
                image_file_name,
                created_name,
                created_email
            FROM view_checklist 
            WHERE product_id = :productId
            ORDER BY category_name, checklist_detail
        ";

        $checklistItems = DbManager::fetchPDOQueryData('dto_configurator', $checklistItemsQuery, [':productId' => $productId])['data'];

        // Step 2: Get progress status for each item (if exists)
        $progressQuery = "
            SELECT 
                checklist_item_id,
                is_active,
                progress_updated_date
            FROM view_project_checklist_progress 
            WHERE project_id = :projectId
        ";

        $progressItems = DbManager::fetchPDOQueryData('dto_configurator', $progressQuery, [':projectId' => $projectId])['data'];

        // Create a lookup array for progress
        $progressLookup = [];
        foreach ($progressItems as $progress) {
            $progressLookup[$progress['checklist_item_id']] = [
                'is_completed' => (bool)$progress['is_active'],
                'completed_date' => $progress['progress_updated_date']
            ];
        }

        // Step 3: Merge checklist items with their progress status
        $result = [];
        foreach ($checklistItems as $item) {
            $itemId = $item['checklist_item_id'];

            $result[] = [
                'id' => $itemId,
                'checklist_item_id' => $itemId,
                'checklist_detail' => $item['checklist_detail'],
                'checklist_detail_html' => $item['checklist_detail_html'],
                'category_id' => $item['category_id'],
                'category_name' => $item['category_name'],
                'image_file_name' => $item['image_file_name'],
                'created_name' => $item['created_name'],
                'created_email' => $item['created_email'],
                'is_completed' => isset($progressLookup[$itemId]) ? $progressLookup[$itemId]['is_completed'] : false,
                'completed_date' => isset($progressLookup[$itemId]) ? $progressLookup[$itemId]['completed_date'] : null,
                'project_id' => $projectId,
                'product_id' => $productId
            ];
        }

        echo json_encode($result);
        exit();
    }

    public function addCheckListItem() {
        $checklistDetail = $_POST['checklistDetail'];
        $checklistDetailHtml = $_POST['checklistDetailHtml'];
        $categorySelect = $_POST['categorySelect'];
        $imageFileName = $_POST['imageFileName'];
        $selectedProducts = explode(',', $_POST['selectedProducts']);

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Add Checklist Item With Following Parameters" . implode('|', $_POST));
        Journals::saveJournal("PROCESSING | Add Checklist Item With Following Parameters" . implode('|', $_POST), PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_PROCESSING, '', "Add Checklist Item");


        if (!SharedManager::hasAccessRight(35, 48)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Add Checklist Item | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Add Checklist Item | ".implode(' | ', $_POST),PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_ERROR, implode(' | ', $_POST), "Add Checklist Item");
            returnHttpResponse(400, "Unauthorized User for Add Checklist Item.");
        }


        // Step 1: Insert image file into server
        if ($imageFileName) {
            $imageFileName = $this->saveChecklistImageToServer($_FILES['image']);
        }

        // Step 2: Insert checklist item
        $query = "INSERT INTO checklist_items(detail, detail_html, category_id, image_file_name, created_name, updated_name, created_email, updated_email)";
        $parameters[] = [$checklistDetail, $checklistDetailHtml, $categorySelect, $imageFileName, SharedManager::$fullname, SharedManager::$fullname, SharedManager::$email, SharedManager::$email];
        $response_insert = DbManager::fetchInsert('dto_configurator', $query, $parameters);
        $lastInsertedChecklistId = $response_insert["pdoConnection"]->lastInsertId();

        // Step 3: Insert checklist item products
        foreach ($selectedProducts as $productId) {
            $params = [];
            $query = "INSERT INTO checklist_product_match(checklist_item_id, product_type_id)";
            $params[] = [$lastInsertedChecklistId, $productId];
            DbManager::fetchInsert('dto_configurator', $query, $params);
        }

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Checklist Item Saved Successfully.").implode(' | ', $_POST);
        Journals::saveJournal("CREATED | Checklist Item Saved Successfully.", PAGE_CHECKLIST, CHECKLIST_ADD_CHECKLIST_ITEM, ACTION_CREATED, implode(' | ', $_POST), "Checklist Item Create");
    }

    public function updateChecklistItem() {
        $id = $_POST['id'];
        $checklistDetail = $_POST['checklistDetail'];
        $checklistDetailHtml = $_POST['checklistDetailHtml'];
        $categorySelect = $_POST['categorySelect'];
        $selectedProducts = explode(',', $_POST['selectedProducts']);

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Update Checklist Item With Following Parameters" . implode('|', $_POST));
        Journals::saveJournal("PROCESSING | Update Checklist Item With Following Parameters" . implode('|', $_POST), PAGE_CHECKLIST, CHECKLIST_EDIT_CHECKLIST_ITEM, ACTION_PROCESSING, '', "Update Checklist Item");

        if (!SharedManager::hasAccessRight(35, 48)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Update Checklist Operation | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Update Checklist Operation | ".implode(' | ', $_POST),PAGE_CHECKLIST, CHECKLIST_EDIT_CHECKLIST_ITEM, ACTION_ERROR, implode(' | ', $_POST), "Update Checklist Item");
            returnHttpResponse(400, "Unauthorized User for Update Checklist Operation.");
        }


        try {
            // Step 1: Handle image upload if new image provided
            $imageFileName = null;
            if (!empty($_FILES['image']['name'])) {
                $imageFileName = $this->saveChecklistImageToServer($_FILES['image']);
            }

            // Step 2: Update checklist item
            $query = "UPDATE checklist_items 
                  SET detail = :detail, detail_html = :detailHtml, category_id = :categoryId, 
                      updated_name = :updatedName, updated_email = :updatedEmail";

            $params = [
                ':detail' => $checklistDetail,
                ':detailHtml' => $checklistDetailHtml,
                ':categoryId' => $categorySelect,
                ':updatedName' => SharedManager::$fullname,
                ':updatedEmail' => SharedManager::$email,
                ':id' => $id
            ];

            // Add image update if new image uploaded
            if ($imageFileName) {
                $query .= ", image_file_name = :imageFileName";
                $params[':imageFileName'] = $imageFileName;
            }

            $query .= " WHERE id = :id";

            DbManager::fetchPDOQueryData('dto_configurator', $query, $params);

            // Step 3: Delete existing product matches
            $deleteQuery = "DELETE FROM checklist_product_match WHERE checklist_item_id = :id";
            DbManager::fetchPDOQueryData('dto_configurator', $deleteQuery, [':id' => $id]);

            // Step 4: Insert new product matches
            foreach ($selectedProducts as $productId) {
                $params = [];
                $insertQuery = "INSERT INTO checklist_product_match(checklist_item_id, product_type_id)";
                $params[] = [$id, $productId];
                DbManager::fetchInsert('dto_configurator', $insertQuery, $params);
            }

            SharedManager::saveLog('log_dtoconfigurator',"UPDATED | Checklist Item Updated Successfully." . implode(' | ', $_POST));
            Journals::saveJournal("UPDATED | Checklist Item Updated Successfully.", PAGE_CHECKLIST, CHECKLIST_EDIT_CHECKLIST_ITEM, ACTION_MODIFIED, implode(' | ', $_POST), "Checklist Item Update");

            echo json_encode(['success' => true, 'message' => 'Checklist item updated successfully']);
            exit();

        } catch (Exception $e) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Update Checklist Item Error: " . $e->getMessage());
            Journals::saveJournal("ERROR | Update Checklist Item Error", PAGE_CHECKLIST, CHECKLIST_EDIT_CHECKLIST_ITEM, ACTION_ERROR, implode(' | ', $_POST), "Update Checklist Item");
            echo json_encode(['success' => false, 'message' => 'Failed to update checklist item']);
            exit();
        }
    }

    private string $basePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\99_DTO_Configurator_Files";
    private string $uploadSubPath = "5_Checklist_Images";
    private array $acceptedTypes = ["jpeg", "jpg", "png"];
    private int $fileSizeLimit = 3145728;
    public function saveChecklistImageToServer($image) {
        if (isset($image) || !empty($image['name'])) {
            $fileInfo = pathinfo($image['name']);
            $extension = strtolower($fileInfo['extension']);

            // Set the upload path
            $uploadPath = "$this->basePath\\$this->uploadSubPath";
            if (!file_exists($uploadPath)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Checklist Image Addition Directory Not Found Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Checklist Image Addition Directory Not Found Error | ".implode(' | ', $_POST), PAGE_CHECKLIST, CHECKLIST_ADD_CHECKLIST_ITEM, ACTION_ERROR, implode(' | ', $_POST), "Checklist Image");
                returnHttpResponse(400, "Directory not found.");
            }

            // Validate the file extension
            if (!in_array($extension, $this->acceptedTypes)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Checklist Image Addition Extension Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Checklist Image Addition Extension Error  | ".implode(' | ', $_POST), PAGE_CHECKLIST, CHECKLIST_ADD_CHECKLIST_ITEM, ACTION_ERROR, implode(' | ', $_POST), "Checklist Image");
                returnHttpResponse(400, "Extension must be " . join(', ', $this->acceptedTypes));
            }

            // Validate the file size
            $fileSize = $image['size'];
            if ($fileSize > $this->fileSizeLimit) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Checklist Image Addition File Size Max 3MB Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Checklist Image Addition File Size Max 3MB Error | ".implode(' | ', $_POST), PAGE_CHECKLIST, CHECKLIST_ADD_CHECKLIST_ITEM, ACTION_ERROR, implode(' | ', $_POST), "Checklist Image");
                returnHttpResponse(400, "File size must be maximum 3MB");
            }

            // Check for file upload errors
            if ($image['error']) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Checklist Image Addition Unexpected Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Checklist Image Addition Unexpected Error | ".implode(' | ', $_POST), PAGE_CHECKLIST, CHECKLIST_ADD_CHECKLIST_ITEM, ACTION_ERROR, implode(' | ', $_POST), "Checklist Image");
                returnHttpResponse(500, "Unexpected error occurs : {$image['name']}");
            }


            $fileName =  date("Ymd_His") . '_' . str_replace(' ', '_', $image['name']);
            $tempPath = $image['tmp_name'];
            $targetPath = "$uploadPath\\$fileName";

            // Move the uploaded file to the target path
            if (!move_uploaded_file($tempPath, $targetPath)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Checklist Image Addition Unexpected Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Checklist Image Addition Unexpected Error | ".implode(' | ', $_POST), PAGE_CHECKLIST, CHECKLIST_ADD_CHECKLIST_ITEM, ACTION_ERROR, implode(' | ', $_POST), "Checklist Image");
                returnHttpResponse(500, "Unexpected error occurs");
            }

            return $fileName;
        }
    }

    public function toggleChecklistItem() {
        $projectId = $_POST['projectId'];
        $checklistItemId = $_POST['checklistItemId'];

        try {
            // Check if progress record exists
            $checkQuery = "
                SELECT id, active 
                FROM checklist_project_progress 
                WHERE project_id = :projectId AND checklist_item_id = :checklistItemId
            ";

            $existingRecord = DbManager::fetchPDOQueryData('dto_configurator', $checkQuery, [
                ':projectId' => $projectId,
                ':checklistItemId' => $checklistItemId
            ])['data'];

            if (!empty($existingRecord)) {
                // Update existing record
                $record = $existingRecord[0];
                $newStatus = $record['active'] ? 0 : 1; // Toggle status

                $updateQuery = "
                    UPDATE checklist_project_progress 
                    SET active = :newStatus, updated_by = :updatedBy 
                    WHERE id = :id
                ";

                DbManager::fetchPDOQueryData('dto_configurator', $updateQuery, [
                    ':newStatus' => $newStatus,
                    ':updatedBy' => SharedManager::$fullname,
                    ':id' => $record['id']
                ])['data'];

                $message = $newStatus ? 'Item marked as completed' : 'Item marked as incomplete';

            } else {
                // Create new progress record
                $insertQuery = "INSERT INTO checklist_project_progress (project_id, checklist_item_id, active, created_by, updated_by)";
                $parameters[] = [$projectId, $checklistItemId, 1, SharedManager::$fullname, SharedManager::$fullname];
                DbManager::fetchInsert('dto_configurator', $insertQuery, $parameters);

                $message = 'Item marked as completed';
            }

            echo json_encode([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            error_log("Error in toggleChecklistItem: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update checklist item'
            ]);
        }
        exit();
    }

    public function deleteChecklistItem(): void {
        $id = $_POST['id'];

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Checklist Item Request With ID : " . $id);
        Journals::saveJournal("PROCESSING | Remove Checklist Item Request With ID : " . $id, PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_PROCESSING, implode(' | ', $_POST), "Checklist Delete");

        if (!SharedManager::hasAccessRight(35, 48)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Delete Checklist Operation | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Delete Checklist Operation | ".implode(' | ', $_POST),PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_ERROR, implode(' | ', $_POST), "Checklist Delete");
            returnHttpResponse(400, "Unauthorized User for Delete Checklist Operation.");
        }

        $query = "SELECT image_file_name FROM checklist_items WHERE id = :id";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id]);
        $imageFileName = $result['data'][0]['image_file_name'] ?? '';

        // Remove from projects
        $query = "DELETE FROM checklist_project_progress WHERE checklist_item_id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id]);

        // Remove from many-to-many relation with products
        $query = "DELETE FROM checklist_product_match WHERE checklist_item_id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id]);

        // Remove from checklist item
        $query = "DELETE FROM checklist_items WHERE id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id]);

        if (!empty($imageFileName)) {
            $uploadPath = "$this->basePath\\$this->uploadSubPath";
            $filePath = "$uploadPath\\$imageFileName";
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"REMOVED | Remove Checklist Item Request Successful With ID  : " . $id);
        Journals::saveJournal("REMOVED | Remove Checklist Item Request Successful With ID : " . $id, PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_DELETED, implode(' | ', $_POST), "Checklist Delete");
    }
}


$controller = new ChecklistController($_POST);

$response = match ($_GET['action']) {
    'getChecklistItem' => $controller->getChecklistItem(),
    'getChecklistCategories' => $controller->getChecklistCategories(),
    'getChecklistProducts' => $controller->getChecklistProducts(),
    'getChecklistItems' => $controller->getChecklistItems(),
    'getProjectChecklistItems' => $controller->getProjectChecklistItems(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'addCheckListItem' => $controller->addCheckListItem(),
    'toggleChecklistItem' => $controller->toggleChecklistItem(),
    'deleteChecklistItem' => $controller->deleteChecklistItem(),
    'updateChecklistItem' => $controller->updateChecklistItem(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};