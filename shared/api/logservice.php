<?php
$table = $_POST['table'];
if (empty($table)) {
    exit;
}
$what = str_replace("+", " ", $_POST['what']);
SharedManager::saveLog($table, $what);
exit();
?>

