<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cronjobs/CronManagers.php';

header('Content-Type:application/json;charset=utf-8;');

$controller = new RPARobotController();
switch ($_GET["action"]){
    case "getNextQueuedRobot":
        $controller->getNextQueuedRobot();
    break;
}

switch ($_POST["action"]){
    case "updateRobotStatus":
        $controller->updateRobotStatus();
    break;
    case "updateRobotRunTime":
        $controller->updateRobotRunTime();
    break;
    default:
    break;
}

Class RPARobotController{
    private string $ROBOT_IP = "140.231.42.187";
    public function __construct(){}

    public function getNextQueuedRobot(){
        if(getUserIP() === $this->ROBOT_IP){
            $runningRobotCountQuery = "SELECT COUNT(*) AS runningRobotCount FROM robots WHERE `Status` = 'running'";
            $runningRobotCountQueryResult = CronDbManager::fetchQueryData('rpa', $runningRobotCountQuery)["data"];
            if($runningRobotCountQueryResult[0]["runningRobotCount"] > 0)
                CronDbManager::fetchQuery('rpa', "UPDATE robots SET `Status`='failed' WHERE `Status` = 'running' AND TableName != 'vfz_robot'");
        }

        $getNextQueuedRobotQuery = "
            SELECT 
               TableName 
            FROM 
               robots 
            WHERE
                `Status` IN('queue','failed') AND 
                `Status` != 'running' AND
                robots.Usage=1 
            ORDER BY 
               Updated ASC 
            LIMIT 1
        ";

        $nextQueuedRobot = CronDbManager::fetchQueryData('rpa', $getNextQueuedRobotQuery)["data"];
        echo json_encode($nextQueuedRobot);
    }

    public function updateRobotStatus(){
        $status = trim(strtolower($_POST["status"]));
        $robotName = trim(strtolower($_POST["robotName"]));

        $acceptedStatusValues = ['success', 'queue', 'running', 'failed'];
        if(!in_array($status, $acceptedStatusValues))
            returnHttpResponse(500, "Unaccepted status value: $status, POST: " . json_encode($_POST));

        $this->checkRobotNameValidity($robotName);

        $query = "UPDATE robots SET status = :status WHERE TableName = :robotName";
        CronDbManager::fetchQuery('rpa', $query, [
            ":status" => $status,
            ":robotName" => $robotName
        ]);
        returnHttpResponse(200, "success");
    }

    public function updateRobotRunTime(){
        $runTime = trim(strtolower($_POST["runTime"]));
        $robotName = trim(strtolower($_POST["robotName"]));

        $replacedRunTime = str_replace(':', '', $runTime);

        if(!is_numeric($replacedRunTime))
            returnHttpResponse(500, "given runtime is not numeric");

        $this->checkRobotNameValidity($robotName);

        $updateQuery = "UPDATE robots SET RunTime = :runTime WHERE TableName = :robotName";
        CronDbManager::fetchQuery('rpa', $updateQuery, [
            ":runTime" => $runTime,
            ":robotName" => $robotName
        ]);
        returnHttpResponse(200, "success");
    }

    private function getRobotNames(){
        $acceptedRobotNamesQuery = "SELECT TableName FROM robots WHERE `Usage` = 1";
        $acceptedRobotNames = CronDbManager::fetchQueryData('rpa', $acceptedRobotNamesQuery)["data"];
        $robotNames = array();

        foreach ($acceptedRobotNames as $key=>$value)
            $robotNames[] = $value["TableName"];

        return $robotNames;
    }

    private function checkRobotNameValidity($robotName){
        $robotNames = $this->getRobotNames();
        if(!in_array($robotName, $robotNames))
            returnHttpResponse(500, "robotName: '$robotName' name does not exists, POST: " . json_encode($_POST));
    }

}