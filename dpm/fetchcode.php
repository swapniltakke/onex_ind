<?php
include 'DatabaseConfig.php';
session_start();
if (!isset($_SESSION['username'])) {
    die("User not logged in.");
}
$user=$_SESSION['username'];
$pass = $_SESSION['pass'];

if (!$conn) {
    die("Connection failed: " . odbc_errormsg());
}



// Query to fetch data
$sql = "SELECT TOP 1 product_name, user_id, station_name, barcode, up_date 
        FROM tbl_transactions 
        WHERE stage_id = '3' 
        ORDER BY up_date DESC";
$stmt = odbc_prepare($conn, $sql);

if (!$stmt) {
    die("Failed to prepare statement: " . odbc_errormsg($conn));
}

$result = odbc_execute($stmt, [$user]);

if (!$result) {
    die("Failed to execute statement: " . odbc_errormsg($conn));
}


$data = [];
while ($row = odbc_fetch_array($stmt)) {
    $data[] = $row['product_name'];
    $data[] = $row['user_id'];
    $data[] = $row['station_name'];
    $data[] = $row['barcode'];
    $data[] = $row['up_date'];

}

odbc_close($conn);
                         
// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
