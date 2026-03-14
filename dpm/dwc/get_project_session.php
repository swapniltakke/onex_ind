<?php
session_start();

// Get the current file name
$currentFile = basename($_SERVER['HTTP_REFERER']);

// Prepare response array
$response = [
    'project' => $_SESSION['selected_project'] ?? '',
    'panel' => $_SESSION['selected_panel'] ?? '',
    'station' => $_SESSION['selected_station'] ?? ''
];

// Add checklist-specific fields if on checklist form
if (strpos($currentFile, 'checklist_form.php') !== false) {
    $response['checklist'] = $_SESSION['selected_checklist'] ?? '';
    $response['line'] = $_SESSION['selected_line'] ?? '';
    $response['product'] = $_SESSION['selected_product'] ?? '';
}

// Return JSON response
echo json_encode($response);
?>