<?php
include('shared/CommonManager.php');
if(isset($_GET['product_id'])) {
    $sql = "delete FROM tbl_product where product_id=:product_id";
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":product_id" => $_GET['product_id']]);
}
$_SESSION['success'] = "<strong>Congratulations</strong> Product information has been deleted successfully.";
header("location:Productlist.php");
?>