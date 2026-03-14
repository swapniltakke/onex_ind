<?php
include('shared/CommonManager.php');
if (isset($_GET['checklist_id'])) {
    $sql = "delete FROM tbl_checklist where id=:checklist_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":checklist_id" => $_GET['checklist_id']]);
}
$_SESSION['success'] = "<strong>Congratulations</strong> Checklist Item information has been deleted successfully.";
header("location:Checklistdetails.php");
?>