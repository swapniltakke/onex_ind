 <?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$ProductinOrder = $_POST['ProductinOrder'];
$sql = "select d.SAPNo,d.Client,SUBSTRING(d.Product, 1, 5) as Product from tbl_DailyUpload d where ProductinOrder=:ProductinOrder";
$resultset = DbManager::fetchPDOQueryData('spectra_db', $sql, [":ProductinOrder" => "$ProductinOrder"])["data"];
$cnt = count($resultset);
if ($cnt > 0) {
  $res['SAPNo'] = $resultset[0]['SAPNo'];
  $res['Product'] = $resultset[0]['Product'];
  $res['Client'] = $resultset[0]['Client'];
  $response['success'] = $res;
} else {
  $response['notexist'] = "NOTEXIST";
}
header("Content-type:application/json");
echo json_encode($response);
die();
?>