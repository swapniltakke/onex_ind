<?php
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

$controller = new TkFormNotesController();

$response = match ($_GET['action']) {
    'getAllTkFormNotesByTkFormId' => $controller->getAllTkFormNotesByTkFormId(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'addNoteToTkForm' => $controller->addNoteToTkForm(),
    'deleteTkNoteById' => $controller->deleteTkNoteById(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

class TkFormNotesController
{
    public function getAllTkFormNotesByTkFormId(): void {
        $tkformId = $_GET['id'];

        $query = "SELECT id, tkform_id, note, user_created, file_name, created
                  FROM tkform_notes 
                  WHERE tkform_id = :tkform_id AND deleted IS NULL 
                  ORDER BY id DESC";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':tkform_id' => $tkformId])['data'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | TK Notes Get All Request With Following Parameters | ".implode(' | ', $_GET));
        Journals::saveJournal("RETURNED | TK Notes Get All Request With Following Parameters | ".implode(' | ', $_GET), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_VIEWED, implode(' | ', $_GET), "Get All TK Notes");

        echo json_encode($data);exit;
    }

    private string $basePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\99_DTO_Configurator_Files";
    private string $uploadSubPath = "2_TK_Note_Images";
    private array $acceptedTypes = ["jpeg", "jpg", "png"];
    private int $fileSizeLimit = 3145728;

    public function addNoteToTkForm(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | TK Note Create Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | TK Note Create Request With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_PROCESSING, implode(' | ', $_POST), "Create TK Note");

        $tkformId = $_POST['id'];
        $note = $_POST['note'];
        $fileName = '';
        $userCreated = SharedManager::$fullname;

        if (isset($_FILES['image']) || !empty($_FILES['image']['name'])) {
            $image = $_FILES['image'];
            $fileInfo = pathinfo($image['name']);
            $extension = strtolower($fileInfo['extension']);

            // Set the upload path
            $uploadPath = "$this->basePath\\$this->uploadSubPath";
            if (!file_exists($uploadPath)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | TK Note Create Error | Directory not found | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | TK Note Create Error | Directory not found | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create TK Note");
                returnHttpResponse(400, "Directory not found.");
            }

            // Validate the file extension
            if (!in_array($extension, $this->acceptedTypes)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | TK Note Create Error | Extension of file error ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | TK Note Create Error | Extension of file error | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create TK Note");
                returnHttpResponse(400, "Extension must be " . join(', ', $this->acceptedTypes));
            }

            // Validate the file size
            $fileSize = $image['size'];
            if ($fileSize > $this->fileSizeLimit) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | TK Note Create Error | File size must be maximum 3MB | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | TK Note Create Error | File size must be maximum 3MB | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create TK Note");
                returnHttpResponse(400, "File size must be maximum 3MB");
            }

            // Check for file upload errors
            if ($image['error']) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | TK Note Create Error | Unexpected error occurs | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | TK Note Create Error | Unexpected error occurs | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create TK Note");
                returnHttpResponse(500, "Unexpected error occurs : {$image['name']}");
            }

            // Set the file name using last six of projectNo and first six of nachbauNo
            $fileName = $tkformId . '_' . date("Ymd_His") . '.' . $extension;
            $tempPath = $image['tmp_name'];
            $targetPath = "$uploadPath\\$fileName";

            // Move the uploaded file to the target path
            if (!move_uploaded_file($tempPath, $targetPath)) {
                error_log("Could not move file from $tempPath to $targetPath. Error: " . error_get_last()["message"]);
                returnHttpResponse(500, "Unexpected error occurs");
            }
        }

        $query = "INSERT INTO tkform_notes(tkform_id, note, file_name, user_created)";
        $parameters[] = [$tkformId, $note, $fileName, $userCreated];
        DbManager::fetchInsert('dto_configurator', $query, $parameters);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | TK Note Successfully Created");
        Journals::saveJournal("CREATED | TK Note Successfully Created", PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_CREATED, implode(' | ', $_POST), "Create TK Note");
    }

    public function deleteTkNoteById(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | TK Note Delete Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | TK Note Delete Request With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_PROCESSING, implode(' | ', $_POST), "Delete TK Note");

        $noteId = $_POST['noteId'];
        $query = "SELECT id, file_name FROM tkform_notes WHERE id = :noteId";
        $tkNote = DbManager::fetchPDOQueryData('dto_configurator', $query, [':noteId' => $noteId])['data'][0] ?? [];

        $query = "UPDATE tkform_notes SET deleted_user = :delUser, deleted = :deletedAt WHERE id = :noteId";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':delUser' => SharedManager::$fullname,':deletedAt' => date('Y-m-d H:i:s'),':noteId' => $noteId]);

        if (!empty($tkNote['file_name'])) {
            $filePath = $this->basePath . "\\" . $this->uploadSubPath . "\\" . $tkNote['file_name'];

            if (file_exists($filePath)) {
                if (!unlink($filePath))
                    returnHttpResponse(400, 'An error occurs while removing the file.');
            } else {
                returnHttpResponse(400, 'The file which will be removed could not found.');
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | TK Note Deleted Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | TK Note Deleted Successfully | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_TK_NOTES, ACTION_PROCESSING, implode(' | ', $_POST), "Delete TK Note");
    }
}
