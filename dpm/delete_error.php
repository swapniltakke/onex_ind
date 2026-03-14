<?php
include('shared/CommonManager.php');
if (isset($_GET['error_id'])) {
    $sql = "delete FROM tbl_error where error_id=:error_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":error_id" => $_GET['error_id']]);
}
$_SESSION['success'] = "<strong>Congratulations</strong> Error has been deleted successfully.";
header("location:Errorlist.php");
?>