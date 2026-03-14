<?php
include('shared/CommonManager.php');
if (isset($_GET['stage_id'])) {
    $sql = "delete FROM tbl_stage where stage_id=:stage_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":stage_id" => $_GET['stage_id']]);
}
$_SESSION['success'] = "<strong>Congratulations</strong> Stage information has been deleted successfully.";
header("location:Stagelist.php");
?>