<?php

$gid = htmlspecialchars($_GET["gid"]);
$file =  $_SERVER["DOCUMENT_ROOT"] . "/users/pics/" . $gid . ".png";
$file_null = $_SERVER["DOCUMENT_ROOT"] . "/users/pics/null.png";

$image = (!file_exists($file) || filesize($file) === 0) ? file_get_contents($file_null) : file_get_contents($file);
header("Content-type: image");
echo $image;
?>
