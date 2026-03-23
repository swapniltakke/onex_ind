<?php
$type = (int) $_GET["type"];
$file = $_GET["file"];
if(!is_numeric($type))
    returnHttpResponse(400, "Invalid type");

$subPath = "";
if($type === 1)
    $subPath = "1_BOM_Note_Images";
else if($type === 2)
    $subPath = "2_TK_Note_Images";
else if($type === 3)
    $subPath = "3_Extension_Note_Images";
else if($type === 4)
    $subPath = "4_Banfomat_Technical_Images";
else if($type === 5)
    $subPath = "5_Checklist_Images";
else
    returnHttpResponse(400, "Invalid type");

$BASE_PATH = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\99_DTO_Configurator_Files";

$path = "$BASE_PATH\\$subPath\\$file";
if(!file_exists($path)){
    $path = "../assets/images/image-not-found.jpg";
}

$mimeType = mime_content_type($path);
header("Content-Type: $mimeType");
echo file_get_contents($path);