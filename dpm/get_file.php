<?php
$file_path = urldecode($_POST['file_path']);
$file_path = str_replace('\\', '/', $file_path);
$file_path = realpath($file_path);

// Check if the file exists
if (file_exists($file_path)) {
    // Get the file extension
    $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

    // Set the appropriate headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    // Output the file contents
    readfile($file_path);
    exit;
} else {
    echo "Error: File not found.";
}
?>