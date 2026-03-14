<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";

header('Content-Type: application/json; charset=utf-8');

// Kullanıcı email (senin yapından geliyor)
$userEmail = SharedManager::$email ?? null;

// LocalStorage stringleri (JSON metin)
$moduleOrder = $_POST['module_order'] ?? ($_GET['module_order'] ?? '[]');
$favModules  = $_POST['fav_modules']  ?? ($_GET['fav_modules']  ?? '[]');

$res = SharedManager::favManager('save_if_changed', [
  'userEmail'   => $userEmail,
  'moduleOrder' => $moduleOrder,
  'favModules'  => $favModules
]);
echo json_encode($res);
