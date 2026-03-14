<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/layout/api/common.php";

header('Content-Type:application/json;charset=utf-8;');

$productionLines = getLines();
echo json_encode($productionLines); exit;