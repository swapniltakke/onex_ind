<?php
include('shared/CommonManager.php');
$station_id = $_POST['station_id'];
$query = "select product_id from tbl_station where station_id=:station_id";
$res = DbManager::fetchPDOQueryData('spectra_db', $query, [":station_id" => "$station_id"])["data"];
$product_id1 = $res[0]['product_id'];
$query1 = "select product_id,product_name from tbl_product where product_id in (:product_id)";
$res1 = DbManager::fetchPDOQueryData('spectra_db', $query1, [":product_id" => "$product_id1"])["data"];
echo "<option value=''>Select Product</option>";
foreach ($res1 as $data) {
    $product_id = $data['product_id'];
    $product_name = $data['product_name'];
    echo "<option value='$product_id'>".$product_name."</option>";
}
?>