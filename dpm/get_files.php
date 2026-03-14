<?php
$directory_path = str_replace('/', '\\', $_POST['directory_path']);
$directory_path = str_replace('+', ' ', $directory_path);

// Function to list files and directories
function listFilesAndDirectories($directory_path) {
    // Open the directory
    $dir = @opendir($directory_path);

    // Check if the directory was opened successfully
    if ($dir === false) {
        echo "Error: Could not open directory '$directory_path'";
        return;
    }

    $output = '';
    // Loop through the contents of the directory
    while (($file = readdir($dir)) !== false) {
        // Skip the "." and ".." directories
        if ($file === '.' || $file === '..') {
            continue;
        }
        if ($_REQUEST['files_of_projects'] == "1") {
            $parts = explode('\\', $directory_path);
            $lastPart = end($parts);
            if ((strpos($directory_path, '15_Photos') !== false) || (strpos($directory_path, '05_SingleLineDiagrams') !== false) || (strpos($directory_path, '04_Correspondence') !== false) || (strpos($directory_path, '03_TestCertificates') !== false)) {
                
            } else {
                if ($file === '03_TestCertificates' || $file === '04_Correspondence' || $file === '05_SingleLineDiagrams' || $file === '15_Photos') {
                    $skip = 0; 
                } else {
                    $skip = 1;
                } 
                if ($skip == 1) {
                    continue;
                }
            }
        }
        // Get the full path of the file/directory
        $full_path = $directory_path . '\\' . $file;

        // Get the file extension
        $file_extension = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));

        // Add the appropriate icon class based on the file type
        $icon_class = '';
        if (is_dir($full_path)) {
            $icon_class = 'fa fa-folder text-primary';
            $output .= "<a href='?directory=" . urlencode($full_path) . "' class='file-link'>";
        } elseif ($file_extension === 'pdf') {
            $icon_class = 'fa fa-file-pdf-o text-danger';
            $output .= "<a href='?file=" . urlencode($full_path) . "' class='file-link'>";
        } elseif ($file_extension === 'xls' || $file_extension === 'xlsx') {
            $icon_class = 'fa fa-file-excel-o text-success';
            $output .= "<a href='?file=" . urlencode($full_path) . "' class='file-link'>";
        } else {
            $icon_class = 'fa fa-file-o text-secondary';
            $output .= "<a href='?file=" . urlencode($full_path) . "' class='file-link'>";
        }

        // Construct the HTML for the file/directory link
        $output .= "<i class='" . $icon_class . "'></i> " . $file;
        $output .= "</a><br>";
    }

    // Close the directory
    closedir($dir);

    return $output;
}

// Call the function to list files and directories
echo listFilesAndDirectories($directory_path);
?>

<style>
.file-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
}

.file-link i {
    margin-right: 10px;
}

.text-primary {
    color: #007bff;
}

.text-danger {
    color: #dc3545;
}

.text-success {
    color: #28a745;
}

.text-secondary {
    color: #6c757d;
}
</style>