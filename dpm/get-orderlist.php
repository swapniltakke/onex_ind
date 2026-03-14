<?php
include('shared/CommonManager.php');
$upload_date = $_POST['upload_date'];
$query1 = "select * from tbl_DailyUpload where DATE(uploaddate) = :upload_date";
$res1 = DbManager::fetchPDOQueryData('spectra_db', $query1, [":upload_date" => date('Y-m-d', strtotime($upload_date))])["data"];

echo "<option value=''>Select Product</option>";
foreach ($res1 as $data) {
    $ProductinOrder = $data['ProductinOrder'];
    echo "<option value='$ProductinOrder'>".$ProductinOrder."</option>";
}
?>