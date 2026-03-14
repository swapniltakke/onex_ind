<?php
error_reporting(E_ERROR | E_PARSE);
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/SharedManager.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/auth/aad.php';
$gate = new AADAuth();