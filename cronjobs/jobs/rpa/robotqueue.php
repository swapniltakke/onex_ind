<?php
/*
 * Task Name: Robot Scheduler
 * Interval: At 02:00 every day
 * Description:
 *
 * */
header('Content-Type: application/json');
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronManagers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

$updateRobotQueueQuery = "UPDATE robots SET `Status` = 'queue' WHERE `Usage` = 1";
CronDbManager::fetchQuery('rpa', $updateRobotQueueQuery);
echo "success";