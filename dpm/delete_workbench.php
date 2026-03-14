<?php
include('shared/CommonManager.php');
if (isset($_GET['workbench_id'])) {
    $sql = "delete FROM tbl_workbench where id=:workbench_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":workbench_id" => $_GET['workbench_id']]);
}
$_SESSION['success'] = "<strong>Congratulations</strong> Workbench information has been deleted successfully.";
header("location:Workbentchlist.php");
?>