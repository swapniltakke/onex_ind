<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php";

// Set the directory path
$directory_path = '\\\\inmumtha111dat\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders';

// Function to list files and directories
function listFilesAndDirectories($directory_path) {
    // Open the directory
    $dir = @opendir($directory_path);

    // Check if the directory was opened successfully
    if ($dir === false) {
        echo "Error: Could not open directory '$directory_path'";
        return;
    }

    // Loop through the contents of the directory
    while (($file = readdir($dir)) !== false) {
        // Skip the "." and ".." directories
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Get the full path of the file/directory
        $full_path = $directory_path . '/' . $file;

        // If it's a directory, print a link to it
        if (is_dir($full_path)) {
            echo "<a href='?directory=$full_path'>$file</a><br>";
        } else {
            // If it's a file, print a link to download/view it
            echo "<a href='?file=$full_path'>$file</a><br>";
        }
    }

    // Close the directory
    closedir($dir);
}

// Check if a new directory was requested
if (isset($_GET['directory'])) {
    $directory_path = $_GET['directory'];
}

// Check if a file was requested
if (isset($_GET['file'])) {
    $file_path = $_GET['file'];

    // Check if the file exists
    if (file_exists($file_path)) {
        // Get the file extension
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

        // Determine the appropriate action based on the file extension
        switch ($file_extension) {
            case 'pdf':
            case 'doc':
            case 'docx':
            case 'xls':
            case 'xlsx':
            case 'ppt':
            case 'pptx':
                // Open the file in the browser
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                readfile($file_path);
                exit;
            default:
                // Download the file
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                readfile($file_path);
                exit;
        }
    } else {
        echo "Error: File not found.";
    }
} else {
    // Call the function to list files and directories
    listFilesAndDirectories($directory_path);
}

exit("swappy");


require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xls;

$reader = new Xls();
$spreadsheet = $reader->load('PO_Text_Database.xls');
$worksheet = $spreadsheet->getActiveSheet();

$data = [];
foreach ($worksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $rowData = [];
    foreach ($cellIterator as $cell) {
        $rowData[] = $cell->getValue();
    }
    $data[] = $rowData;
}

// SharedManager::print($data);

// Get the existing PTD numbers from the mlfb_details table
$existingPTDQuery = "SELECT ptd_no FROM mlfb_details";
$existingPTDNumbers = DbManager::fetchPDOQuery('spectra_db', $existingPTDQuery)["data"];
// SharedManager::print($existingPTDNumbers);
 exit("swapnil");
$updatebreakerQuery = "
    UPDATE mlfb_details
    SET plant = :plant,
        language = :language,
        material_description_new = :material_description_new,
        po_text = :po_text,
        cron_updated = :cron_updated
    WHERE ptd_no = :ptd_no
";

for ($i = 0; $i < count($data); $i++) {
    $ptdNo = $data[$i][0];
    $existingPTDNoArray = array_column($existingPTDNumbers, 'ptd_no');
    if (in_array($ptdNo, $existingPTDNoArray)) {
        DbManager::fetchPDOQuery('spectra_db', $updatebreakerQuery, [
            ':plant' => $data[$i][1],
            ':language' => $data[$i][2],
            ':material_description_new' => $data[$i][3],
            ':po_text' => $data[$i][4],
            ':cron_updated' => '1',
            ':ptd_no' => $ptdNo
        ]);
    }
}

exit("takke");

// Define the secret key as a constant
define('SECRET_KEY', getenv("DB_CREDENTIALS_THA"));

// Function to encrypt the password
function encryptPassword($password) {
    $encryptionMethod = "AES-256-CBC";
    $iv = openssl_cipher_iv_length($encryptionMethod);
    $encryptedPassword = openssl_encrypt($password, $encryptionMethod, SECRET_KEY, 0, $iv);
    return $encryptedPassword;
}

function decryptPassword($encryptedPassword) {
    $encryptionMethod = "AES-256-CBC";
    $iv = openssl_cipher_iv_length($encryptionMethod);
    $decryptedPassword = openssl_decrypt($encryptedPassword, $encryptionMethod, SECRET_KEY, 0, $iv);
    return $decryptedPassword;
}

// $password = "Wswb&sd9";
// $encryptedPassword = encryptPassword($password);
// echo "encryptedPassword - ".$encryptedPassword."<br>";
// $decryptedPassword  = decryptPassword($encryptedPassword);
// echo "decryptedPassword  - ".$decryptedPassword ."<br>";
exit("check code");
$main_query = "SELECT * FROM tbl_user_login_10_02_2025 where user_string!=''";
$main_response = DbManager::fetchPDOQueryData('spectra_db', $main_query)["data"];

foreach ($main_response as $data) {
    $encryptedUserString = "";
    $encryptedPassword = "";
    $encryptedUserString = encryptPassword($data['user_string']);
    $encryptedPassword = encryptPassword($data['password']);

    $query_update = "
                UPDATE tbl_user_login_10_02_2025 SET
                user_string=:user_string
                WHERE user_id =:id
            ";
            $response_update_query = DbManager::fetchPDOQuery('spectra_db', $query_update, 
            [":user_string" => $encryptedUserString, ":id" => $data['user_id']]);
}

exit;

// Adding user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Encrypt the password
    $encryptedPassword = encryptPassword($password);

    // Insert the user details into the database
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $encryptedPassword);
    $stmt->execute();
}

// Login process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $roleId = $_POST["role_id"];

    // Encrypt the user-provided password
    $encryptedPassword = encryptPassword($password);

    // Fetch the user from the database
    $sql = "SELECT * FROM tbl_user_login_06_02_2025 WHERE user_name = ? AND password = ? AND role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $encryptedPassword, $roleId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login successful
        $row = $result->fetch_assoc();
        // Handle the user data as needed
    } else {
        // Login failed
    }
}

?>