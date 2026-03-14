<?php
include('shared/CommonManager.php');
if (isset($_GET['role_id'])) {
    $sql = "delete FROM tbl_roles where role_id=:role_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":role_id" => $_GET['role_id']]);
    // mysqli_query($conn,"ALTER TABLE tbl_roles AUTO_INCREMENT = 1");
}
$_SESSION['success'] = "<strong>Congratulations</strong> Roles information has been deleted successfully.";
header("location:manage_roles_details.php");
?>