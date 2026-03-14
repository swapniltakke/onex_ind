<?php
include('shared/CommonManager.php');
if (isset($_GET['station_id'])) {
    $sql = "delete FROM tbl_station where station_id=:station_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":station_id" => $_GET['station_id']]);
}
$_SESSION['success'] = "<strong>Congratulations</strong> Station information has been deleted successfully.";
header("location:Stationlist.php"); 
?>