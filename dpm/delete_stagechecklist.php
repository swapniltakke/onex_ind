<?php
include('shared/CommonManager.php');
if (isset($_GET['id'])) {
    $sql = "delete FROM tbl_checklistdetails where check_id=:check_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":check_id" => $_GET['id']]);
}
$_SESSION['success'] = "<strong>Congratulations</strong> Checklist information has been deleted successfully.";
header("location:stagewisechecklistdet.php");
?>