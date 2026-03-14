<?php
// Start output buffering to prevent accidental output
ob_start();
session_start();

// Security headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 204 No Content");
    ob_end_clean();
    exit;
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header("Content-Type: application/json");
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    ob_end_flush();
    exit;
}

// Function to handle session updates
function updateSession($data) {
    // Always update project if provided and not empty
    if (isset($data['project']) && $data['project'] !== '') {
        $_SESSION['selected_project'] = $data['project'];
    }

    // Update panel if provided
    if (isset($data['panel'])) {
        $_SESSION['selected_panel'] = $data['panel'];
    }

    // Update station if provided
    if (isset($data['station'])) {
        $_SESSION['selected_station'] = $data['station'];
    }

    // Update checklist-specific fields if they exist
    if (isset($data['checklist'])) {
        $_SESSION['selected_checklist'] = $data['checklist'];
    }
    if (isset($data['line'])) {
        $_SESSION['selected_line'] = $data['line'];
    }
    if (isset($data['product'])) {
        $_SESSION['selected_product'] = $data['product'];
    }

    // Clear session if requested
    if (isset($data['clear']) && $data['clear'] == 1) {
        unset($_SESSION['selected_panel']);
        unset($_SESSION['selected_checklist']);
        unset($_SESSION['selected_line']);
        unset($_SESSION['selected_product']);
        unset($_SESSION['selected_station']);
    }
}

try {
    // Sanitize and validate inputs with more robust filtering
    $project = filter_input(INPUT_POST, 'project', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ($_SESSION['selected_project'] ?? '');
    $panel = filter_input(INPUT_POST, 'panel', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ($_SESSION['selected_panel'] ?? '');
    $checklist = filter_input(INPUT_POST, 'checklist', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ($_SESSION['selected_checklist'] ?? '');
    $line = filter_input(INPUT_POST, 'line', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ($_SESSION['selected_line'] ?? '');
    $product = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ($_SESSION['selected_product'] ?? '');
    $station = filter_input(INPUT_POST, 'station', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ($_SESSION['selected_station'] ?? '');
    $clear = filter_input(INPUT_POST, 'clear', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $is_scan = 0;

    // Handle scanned values
    if (strpos($project, "*") !== false) {
        $explodedValues = explode("*", $project);
        $projectNo = $explodedValues[0];
        $panelNumber = str_pad($explodedValues[1], 6, "0", STR_PAD_LEFT);
        $panelNumber = $panelNumber."|".$explodedValues[6]."|".$explodedValues[5];
        $is_scan = 1;
    } else {
        $projectNo = $project;
        $panelNumber = "";
        unset($_SESSION['is_scan']);
    }

    if ($panelNumber !== "") {
        // $panelNumber is already set from the exploded array
    } else {
        $panelNumber = $panel;
    }

    // Prepare data for session update
    $sessionData = [
        'project' => $projectNo,
        'panel' => $panelNumber,
        'checklist' => $checklist,
        'line' => $line,
        'product' => $product,
        'station' => $station,
        'clear' => $clear
    ];
    
    // Update session
    updateSession($sessionData);

    // Set scan flag if applicable
    if ($is_scan) {
        $_SESSION['is_scan'] = $is_scan;
    }

    // Prepare response data
    $responseData = [
        'status' => 'success',
        'message' => 'Session updated successfully',
        'data' => [
            'project' => $projectNo,
            'panel' => $panelNumber,
            'checklist' => $checklist,
            'line' => $line,
            'product' => $product,
            'station' => $station
        ]
    ];

    // Send success response
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($responseData);

} catch (Exception $e) {
    // Log the error (implement proper logging)
    error_log('Session update error: ' . $e->getMessage());

    // Send error response
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}

// Ensure all output is sent
ob_end_flush();
exit;
?>