<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
header('Content-Type:application/json;charset=utf-8;');

$type = strtolower($_GET["type"]);
if(!in_array($type, ["lv", "mv"]))
    returnHttpResponse(400, "Bad input");

function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => '<span data-translate="year">year(s)</span>',
        'm' => '<span data-translate="months">month(s)</span>',
        'w' => '<span data-translate="weeks">week(s)</span>',
        'd' => '<span data-translate="days">day(s)</span>',
        'h' => '<span data-translate="hours">hour(s)</span>',
        'i' => '<span data-translate="minutes">minute(s)</span>',
        's' => '<span data-translate="seconds">second(s)</span>',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' <span data-translate="ago">ago</span>' : 'ago';
}

$lastUpdate = "
    SELECT 
        Max(Created) AS 'created' 
    FROM assembly_plan_mv
    WHERE revisionNr = (SELECT max(revisionNr) from assembly_plan_mv_index)
";
$result = DbManager::fetchPDOQueryData('planning', $lastUpdate)["data"][0];
$created = $result['created'];
$last_update = strtotime($created);
$last_update2 = time_elapsed_string($created);

echo json_encode([
    "last_update" =>  date("d-m-Y H:i",$last_update),
    "last_update2" => $last_update2
]); exit;

