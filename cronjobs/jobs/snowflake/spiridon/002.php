<?php
ini_set('memory_limit', '8192M');
ini_set('max_execution_time', 0);

require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronDbManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeQuery.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/spiridon/rpa_common.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";

function executeAndFetchResults($queryObject) {
    $queryId = null;
    $queryStatus = null;

    while ($queryObject->nextPartition()) {
        flush();
        try {
            $currentPartitionData = $queryObject->getCurrentPartitionData();
            if (isset($currentPartitionData['code']) && $currentPartitionData['code'] === "333334") {
                if ($queryId === null) {
                    $queryId = $currentPartitionData['statementHandle'];
                    $queryStatus = "RUNNING";
                }

                $queryStatusUrl = $currentPartitionData['statementStatusUrl'];
                $queryStatusResponse = file_get_contents($queryStatusUrl);
                $queryStatusData = json_decode($queryStatusResponse, true);

                if ($queryStatusData['status'] === "RUNNING") {
                    sleep(10);
                    continue;
                } elseif ($queryStatusData['status'] === "FAILED") {
                    handleQueryFailure($queryStatusData['message']);
                    break;
                } elseif ($queryStatusData['status'] === "SUCCEEDED") {
                    $queryStatus = "SUCCEEDED";
                }
            }

            $currentPartitionNumber = $queryObject->getCurrentPartitionNumber();
            if ($currentPartitionData) {
                saveTempDataFile($GLOBALS['TABLE_NAME'], $currentPartitionData, $currentPartitionNumber);
            }
            unset($currentPartitionData);

        } catch (Exception $e) {
            handleQueryFailure($e->getMessage());
            break;
        }
    }

    if ($queryStatus === "SUCCEEDED") {

    } else {
        handleQueryFailure("Query status: $queryStatus");
    }
}

$TABLE_NAME = "sap_spiridon_002";
$TEMP_TABLE_NAME = "$TABLE_NAME" . "_temp";
$now = date("Ymd_His");
$BACKUP_TABLE_NAME = "$TABLE_NAME" . "_$now";
$BATCH_SIZE = 15000;

function getInsertToTempQuery()
{
    global $TEMP_TABLE_NAME;

    return "
        INSERT INTO $TEMP_TABLE_NAME(
            OrderNumber,
            SalesOrder,
            MaterialNumber2,
            MaterialDescription,
            ReqQuantity,
            QuantityWithdrawn,
            MaterialNumber,
            BUn,
            MRP,
            IndicatorBackflush,
            Typ,
            MaterialGrouping,
            ItemCategory,
            StorageLocation,
            KMATNo,
            KMATName,
            ItemNumber,
            ShortText,
            Del
        ) VALUES
        ";
}

deleteTempFiles($TABLE_NAME);

$spiridon002TableContents = "
    `id` INT NOT NULL AUTO_INCREMENT,
    `OrderNumber` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `SalesOrder` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `MaterialNumber2` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `MaterialDescription` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `ReqQuantity` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `QuantityWithdrawn` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `MaterialNumber` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `BUn` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `MRP` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `IndicatorBackflush` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `Typ` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `MaterialGrouping` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `ItemCategory` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `StorageLocation` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `KMATNo` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `KMATName` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ItemNumber` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `ShortText` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `Del` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb3_general_ci',
    `Created` DATETIME NULL DEFAULT (CURRENT_TIMESTAMP),
    `Updated` DATETIME NULL DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
    ";

$createTempTableQuery = "
    CREATE TABLE IF NOT EXISTS `$TEMP_TABLE_NAME` (
        $spiridon002TableContents
    );
";
CronDbManager::fetchQuery('rpa', $createTempTableQuery);

$truncate002TempTableQuery = "TRUNCATE $TEMP_TABLE_NAME";
CronDbManager::fetchQuery('rpa', $truncate002TempTableQuery);

$createTableQuery = "
    CREATE TABLE IF NOT EXISTS `$TABLE_NAME` (
        $spiridon002TableContents
    );
";
CronDbManager::fetchQuery('rpa', $createTableQuery);

$salesOrders = getProjects();
$salesOrdersChunks = array_chunk($salesOrders, 10);

foreach ($salesOrdersChunks as $chunk)
{
    sleep(10);
    $salesOrdersWhereClause = "'" . implode("','", $chunk) . "'";
    $spiridon002SnowflakeQuery = "
        SELECT
        PLAF.KDAUF AS SALES_ORDER,
        PLAF.KDPOS AS SALES_ORDER_ITEM,
        RESB.MATNR AS MATERIALNUMBER2,
        MAKT_A.MAKTX AS MATERIAL_DESCRIPTION,
        PLAF.DISPO AS MRP_CONTROLLER,
        PLAF.LGORT AS STORAGE_LOCATION,
        RESB.BDMNG AS QTY,
        RESB.ENMNG AS WITHDRAWN_QUANTITY,
        PLAF.MEINS AS BUN,
        RESB.BAUGR AS MATERIAL_NUMBER,
        RESB.RGEKZ as BACKFLUSH_INDICATOR,
        PLAF.MATNR as KMAT_NO,
        MAKT_B.MAKTX as KMAT_NAME,
        PLAF.PLNUM AS ORDER_NUMBER,
        RESB.POSTP AS ITEM_CATEGORY,
        PLAF.PWWRK AS PLANT,
        RESB.XLOEK AS DEL,
        MARC.DISMM AS TYP,
        MARC.MATGR as MATERIAL_GROUPING,
        MARA.MTART AS MATERIAL_TYPE,
        RESB.RSNUM AS RESERVATIONNO,
        RESB.RSPOS AS ITEMNUMBEROFRESERVATION,
        RESB.BDTER AS REQUIREMENT_DATE,
        RESB.POTX1 AS ITEM_TEXT_LINE
        FROM(
        SELECT KDAUF, KDPOS, PLNUM, PWWRK, DISPO, LGORT,
        MEINS, MANDT, PLWRK, MATNR
        FROM PRD_DISTRIBUTE.ERP_RAW_AP001.PLAF
        WHERE
        PLAF.KDAUF LIKE ANY ($salesOrdersWhereClause)
        AND PLAF.PLWRK = '9E62'
        AND PLAF.PWWRK = '9E62'
        AND PLAF.MANDT = '100'
        ) AS PLAF
        JOIN PRD_DISTRIBUTE.ERP_RAW_AP001.RESB
        ON RESB.PLNUM = PLAF.PLNUM
        AND RESB.XLOEK = ''            -- W/O DEL.FLAG FOR
        AND RESB.MANDT = PLAF.MANDT    -- CLIENT
        AND RESB.WERKS = PLAF.PLWRK    -- PLANT
        JOIN PRD_DISTRIBUTE.ERP_RAW_AP001.MARC
        ON MARC.MATNR = RESB.MATNR
        AND MARC.MANDT = RESB.MANDT
        AND RESB.WERKS = MARC.WERKS
        JOIN PRD_DISTRIBUTE.ERP_RAW_AP001.MARA
        ON MARC.MATNR = MARA.MATNR
        AND MARC.MANDT = MARA.MANDT
        JOIN PRD_DISTRIBUTE.ERP_RAW_AP001.MAKT as MAKT_A
        ON MAKT_A.MATNR = MARA.MATNR
        AND MAKT_A.MANDT = MARA.MANDT
        AND MAKT_A.SPRAS = 'E'
        JOIN PRD_DISTRIBUTE.ERP_RAW_AP001.MAKT as MAKT_B
        ON MAKT_B.MATNR = PLAF.MATNR
        AND MAKT_B.MANDT = PLAF.MANDT
        AND MAKT_B.SPRAS = 'E';
    ";
    
    $snowflakeQueryObject = new SnowflakeQuery("ONEX", "PRD_DISTRIBUTE", "ERP_RAW_AP001", $spiridon002SnowflakeQuery, [], false, ["batchSize" => 15]);

    try {
        executeAndFetchResults($snowflakeQueryObject);
    } catch (Exception $e) {
        handleQueryFailure($e->getMessage());
    }

    $tempDataPath = getTempDataFileSavePath();
    $folderContent = scandir($tempDataPath);
    if ($folderContent === false) returnHttpResponse(500, "Path: '$tempDataPath' does not exists ");

    $tempDataFiles = array_filter($folderContent, function ($file) use ($TABLE_NAME) {
        return str_starts_with($file, $TABLE_NAME) && !in_array($file, [".", ".."]);
    });

    $insertToTempQuery = getInsertToTempQuery();
    $insertToTempQueryParameters = [];
    $insertCount = 0;

    foreach ($tempDataFiles as $tempDataFile)
    {
        $tempDataFilePath = "$tempDataPath/$tempDataFile";
        $fileContent = file_get_contents($tempDataFilePath);
        $data = json_decode($fileContent, true);
        unset($fileContent);

        foreach ($data as $row)
        {
            $ORDER_NUMBER = htmlspecialchars($row["ORDER_NUMBER"]);
            $SALES_ORDER = htmlspecialchars($row["SALES_ORDER"]);
            $MATERIALNUMBER2 = htmlspecialchars($row["MATERIALNUMBER2"]);
            $MATERIAL_DESCRIPTION = htmlspecialchars($row["MATERIAL_DESCRIPTION"]);
            $QTY = htmlspecialchars($row["QTY"]);
            $WITHDRAWN_QUANTITY = htmlspecialchars($row['WITHDRAWN_QUANTITY']);
            $MATERIAL_NUMBER = htmlspecialchars($row["MATERIAL_NUMBER"]);
            $BUN = htmlspecialchars($row["BUN"]);
            $MRP_CONTROLLER = htmlspecialchars($row["MRP_CONTROLLER"]);
            $BACKFLUSH_INDICATOR = htmlspecialchars($row["BACKFLUSH_INDICATOR"]);
            $TYP = htmlspecialchars($row['TYP']);
            $MATERIAL_GROUPING = htmlspecialchars($row["MATERIAL_GROUPING"]);
            $ITEM_CATEGORY = htmlspecialchars($row["ITEM_CATEGORY"]);
            $STORAGE_LOCATION = htmlspecialchars($row["STORAGE_LOCATION"]);
            $KMAT_NO = htmlspecialchars($row["KMAT_NO"]);
            $KMAT_NAME = htmlspecialchars($row["KMAT_NAME"]);
            $ITEM = htmlspecialchars($row["SALES_ORDER_ITEM"]);
            $DEL = htmlspecialchars($row['DEL']);
            $ITEM_TEXT_LINE = htmlspecialchars($row['ITEM_TEXT_LINE']);

            if(str_ends_with($QTY, '.000'))
                $QTY = preg_replace('/\.000$/', '', $QTY);
            if(str_ends_with($WITHDRAWN_QUANTITY, '.000'))
                $WITHDRAWN_QUANTITY = preg_replace('/\.000$/', '', $WITHDRAWN_QUANTITY);
            if($BUN === "ST")
                $BUN = "PC";

            $shortTextKey = ":$insertCount" . "shortText";
            $insertToTempQueryParameters[] = [$shortTextKey => htmlspecialchars($MATERIAL_DESCRIPTION)];

            $insertToTempQuery .= "(
                '$ORDER_NUMBER',
                '$SALES_ORDER',
                '$MATERIALNUMBER2',
                $shortTextKey,
                '$QTY',
                '$WITHDRAWN_QUANTITY',
                '$MATERIAL_NUMBER',
                '$BUN',
                '$MRP_CONTROLLER',
                '$BACKFLUSH_INDICATOR',
                '$TYP',
                '$MATERIAL_GROUPING',
                '$ITEM_CATEGORY',
                '$STORAGE_LOCATION',
                '$KMAT_NO',
                '$KMAT_NAME',
                '$ITEM',
                '$ITEM_TEXT_LINE',
                '$DEL'
            ),"; 
            $insertCount++;

            if ($insertCount === $BATCH_SIZE) {
                $insertToTempQuery = rtrim($insertToTempQuery, ',');
                CronDbManager::fetchQuery('rpa', $insertToTempQuery, $insertToTempQueryParameters, [], false, false);
                $insertToTempQuery = getInsertToTempQuery();
                $insertToTempQueryParameters = [];
                $insertCount = 0;
            }
        }
        unlink($tempDataFilePath);
    }
    
    if ($insertCount > 0) {
        $insertToTempQuery = rtrim($insertToTempQuery, ',');
        CronDbManager::fetchQuery('rpa', $insertToTempQuery, $insertToTempQueryParameters, [], false, false);
    }
}

$addTempTableKeysQuery = "ALTER TABLE $TEMP_TABLE_NAME 
    ADD INDEX `ix_sap_spiridon_002_OrderNumber` (`OrderNumber`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_SalesOrder` (`SalesOrder`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_MaterialNumber2` (`MaterialNumber2`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_MaterialDescription` (`MaterialDescription`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_ReqQuantity` (`ReqQuantity`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_QuantityWithdrawn` (`QuantityWithdrawn`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_MaterialNumber` (`MaterialNumber`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_BUn` (`BUn`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_MRP` (`MRP`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_IndicatorBackflush` (`IndicatorBackflush`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_Typ` (`Typ`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_MaterialGrouping` (`MaterialGrouping`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_ItemCategory` (`ItemCategory`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_StorageLocation` (`StorageLocation`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_KMATNo` (`KMATNo`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_KMATName` (`KMATName`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_ItemNumber` (`ItemNumber`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_ShortText` (`ShortText`) USING BTREE,
    ADD INDEX `ix_sap_spiridon_002_Del` (`Del`) USING BTREE";    

CronDbManager::fetchQuery('rpa', $addTempTableKeysQuery);

$getPrevBackupTablesQuery = "SELECT TABLE_NAME FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = 'rpa' 
    AND TABLE_NAME LIKE 'sap_spiridon_002_%' 
    AND TABLE_NAME NOT IN('$TEMP_TABLE_NAME', '$BACKUP_TABLE_NAME')";

$prevBackupTables = CronDbManager::fetchQueryData('rpa', $getPrevBackupTablesQuery)["data"];
foreach ($prevBackupTables as $row) {
    $prevBackupTable = $row["TABLE_NAME"];
    $dropPrevBackupTableQuery = "DROP TABLE IF EXISTS $prevBackupTable";
    CronDbManager::fetchQuery('rpa', $dropPrevBackupTableQuery);
}

$renameTablesQuery = "RENAME TABLE $TABLE_NAME TO $BACKUP_TABLE_NAME, $TEMP_TABLE_NAME TO $TABLE_NAME;";
CronDbManager::fetchQuery('rpa', $renameTablesQuery);

function handleQueryFailure($status) {
    echo "Query failed with status: $status";
}
?>