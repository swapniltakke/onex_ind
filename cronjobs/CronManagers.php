<?php

error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set(@date_default_timezone_get());

require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronAuth.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronDbManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronMailManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronFunctions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronMtoolManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";