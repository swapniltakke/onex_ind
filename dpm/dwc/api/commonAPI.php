<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";

$type = $_GET["type"] ?? $_POST["type"];
switch ($type) {
    case "check_session":
        checkSession();
        break;
    default:
        break;
}
exit;

function checkSession()
{
    $response = [
        'isLoggedIn' => false,
        'message' => ''
    ];

    // Check if username exists in session
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $response['isLoggedIn'] = true;
        $response['message'] = 'User is logged in';
    } else {
        $response['message'] = 'User is not logged in';
    }

    echo json_encode($response);
    exit;
}
?>