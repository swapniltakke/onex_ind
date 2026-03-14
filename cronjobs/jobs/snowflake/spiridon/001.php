<?php
ini_set('memory_limit', '8192M');
ini_set('max_execution_time', 0);

require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronDbManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeQuery.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/spiridon/rpa_common.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";

// Define the executeAndFetchResults function
function executeAndFetchResults($queryObject) {
    $queryId = null;
    $queryStatus = null;
    $maxRetries = 30; // 150 seconds total wait time (30 * 5s)

    while ($queryObject->nextPartition()) {
        flush(); // Ensure output is sent and received continuously
        try {
            $currentPartitionData = $queryObject->getCurrentPartitionData();

            // Check for async errors
            if (isset($currentPartitionData['code']) && $currentPartitionData['code'] === "333334") {
                $queryId = $currentPartitionData['statementHandle'];
                $retryCount = 0;
                
                do {
                    sleep(10);
                    $queryStatusUrl = $currentPartitionData['statementStatusUrl'];
                    $queryStatusResponse = file_get_contents($queryStatusUrl);
                    $queryStatusData = json_decode($queryStatusResponse, true);

                    if ($queryStatusData['status'] === "SUCCEEDED") {
                        // Get successful result
                        $currentPartitionData = $queryObject->getCurrentPartitionData();
                        break;
                    } elseif ($queryStatusData['status'] === "FAILED") {
                        handleQueryFailure($queryStatusData['message']);
                        return false;
                    }

                    $retryCount++;
                } while ($retryCount < $maxRetries && $queryStatusData['status'] === "RUNNING");

                if ($retryCount >= $maxRetries) {
                    handleQueryFailure("Query timed out after " . ($maxRetries * 5) . " seconds");
                    return false;
                }
            }

            // Process successful data
            $currentPartitionNumber = $queryObject->getCurrentPartitionNumber();
            if ($currentPartitionData) {
                saveTempDataFile($GLOBALS['TABLE_NAME'], $currentPartitionData, $currentPartitionNumber);
            }
            unset($currentPartitionData);

        } catch (Exception $e) {
            handleQueryFailure($e->getMessage());
            return false;
        }
    }
    return true;
}

$TABLE_NAME = "sap_spiridon_001";
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
        SeqNumberOrder,
        SalesOrder,
        ItemNumber,
        ProfitCenter,
        Plant,
        MaterialNumber,
        ReqType,
        ReqQuantity,
        BUn,
        ReqDate,
        PhantomItemIndicator,
        QuantityWithdrawn,
        BUn2,
        IndicatorIntraMaterial,
        FinalIssueOfReser,
        StorageLocation,
        MaterialNumber2,
        ItemCategory,
        BomItemText,
        SupplyArea,
        IndicatorBackflush,
        RecordType,
        NumberOfReservation,
        ItemNumberOfReservation,
        LatestReqDate,
        IndicatorBulkMaterial,
        SpecialStockIndicator,
        ItemTextIndicator,
        MissingPart,
        ItemIsDeleted,
        GoodsMovementForReservation,
        MRP,
        LanguageKey,
        MaterialGroup,
        MaterialGrouping,
        BomItemNumber,
        Typ,
        KMATNo,
        KMATName,
        MaterialDescription
        ) VALUES";
}

deleteTempFiles($TABLE_NAME);

$spiridon001TableContents = "
    `id` INT NOT NULL AUTO_INCREMENT,
    `OrderNumber` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `SeqNumberOrder` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `SalesOrder` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ItemNumber` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ProfitCenter` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `Plant` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `MaterialNumber` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ReqType` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ReqQuantity` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `BUn` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ReqDate` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `PhantomItemIndicator` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `QuantityWithdrawn` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `BUn2` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `IndicatorIntraMaterial` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `FinalIssueOfReser` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `StorageLocation` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `MaterialNumber2` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ItemCategory` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `BomItemText` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `SupplyArea` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `IndicatorBackflush` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `RecordType` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `NumberOfReservation` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ItemNumberOfReservation` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `LatestReqDate` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `IndicatorBulkMaterial` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `SpecialStockIndicator` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ItemTextIndicator` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `MissingPart` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `ItemIsDeleted` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `GoodsMovementForReservation` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `MRP` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `LanguageKey` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `MaterialGroup` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `MaterialGrouping` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `BomItemNumber` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `Typ` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `MaterialDescription` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `KMATNo` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `KMATName` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_as_ci',
    `Created` DATETIME NULL DEFAULT (CURRENT_TIMESTAMP),
    `Updated` DATETIME NULL DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
    ";

$createTempTableQuery = "
    CREATE TABLE IF NOT EXISTS `$TEMP_TABLE_NAME` (
        $spiridon001TableContents
    );
";
CronDbManager::fetchQuery('rpa', $createTempTableQuery);

$truncate001TempTableQuery = "TRUNCATE $TEMP_TABLE_NAME";
CronDbManager::fetchQuery('rpa', $truncate001TempTableQuery);

$createTableQuery = "
    CREATE TABLE IF NOT EXISTS `$TABLE_NAME` (
        $spiridon001TableContents
    );
";
CronDbManager::fetchQuery('rpa', $createTableQuery);

$salesOrders = getProjects();
$salesOrdersChunks = array_chunk($salesOrders, 10);

foreach ($salesOrdersChunks as $chunk)
{
    sleep(15);
    $salesOrdersWhereClause = "'" . implode("','", $chunk) . "'";
    $spiridon001SnowflakeQuery = "
        with 
        aufk as(
        select * from PRD_DISTRIBUTE.ERP_RAW_AP001.AUFK where BUKRS = '5655' and WERKS = '9E62'
        ),
        resb as (
        select * from PRD_DISTRIBUTE.ERP_RAW_AP001.RESB
        where werks = '9E62' and saknr is not null and xloek <> 'X'
        ),
        makt as (select * from PRD_DISTRIBUTE.ERP_RAW_AP001.MAKT where spras = 'E'),
        marc as (select * from PRD_DISTRIBUTE.ERP_RAW_AP001.MARC where mandt = '100' and werks='9E62'),
        t024d as (select * from PRD_DISTRIBUTE.ERP_RAW_AP001.T024D where mandt = '100' and werks='9E62'),
        t457t as (select * from PRD_DISTRIBUTE.ERP_RAW_AP001.T457T where mandt = '100' and spras = 'E'),
        final as (
        select 
        aufk.AUFNR as prod_order_no,
        aufk.WERKS as plant,
        aufk.kdauf as sales_order_no,
        aufk.kdpos as sales_order_item_number,
        resb.baugr as higher_bom,
        resb.matnr as material,
        makt_a.maktx as material_desc,
        resb.rspos as item_no,
        resb.bdmng as requirement_quantity,
        resb.enmng as withdrawn_quantity,
        resb.lgort as resb_storage_location,
        resb.rgekz as backflush_indicator,
        resb.bdter as date_of_requirement,
        resb.postp as item_category,
        resb.sobkz as special_stock,
        iff(resb.meins = 'ST', 'PC', resb.meins) as base_unit,
        marc.DISPO as mrp_controller,
        marc.MATGR as material_grouping,
        t024d.dsnam as controller_name,
        mara.mtart as material_type,
        afko.plnbez as kmat_number,
        makt_b.maktx as kmat_name,
        resb.STLNR,
        t457t.delkz as t4type,
        t457t.delb0 as maintype,
        resb.XLOEK as deletion_ind,
        aufk.prctr as profit_center,
        resb.dumps as phantom_item_indicator,
        resb.inpos as intra_material_indicator,
        resb.rsnum as reservation_number,
        resb.rspos as reservation_item_number,
        resb.kzear as final_issue_reservation,
        resb.potx1 as bom_item_text,
        resb.prvbe as supply_area,
        resb.rsart as record_type,
        resb.sbter as latest_requirement_date,
        resb.schgt as bulk_material_indicator,
        resb.txtps as item_text_indicator,
        resb.xfehl as missing_part,
        resb.xloek as item_is_deleted,
        resb.xwaok as goods_movement_for_reservation,
        resb.plnfl as sequence_order_number,
        resb.bdart as requirement_type,
        resb.ltxsp as language_key,
        resb.matkl as material_group,
        resb.posnr as bom_item_number
        from aufk 
        left join resb on aufk.aufnr = resb.aufnr and resb.mandt = aufk.mandt
        inner join afko on aufk.mandt = afko.mandt and aufk.aufnr = afko.aufnr
        inner join makt as makt_a on makt_a.matnr = resb.matnr and resb.mandt = makt_a.mandt
        left join makt as makt_b on makt_b.mandt = afko.mandt and makt_b.matnr = afko.plnbez
        inner join marc on resb.matnr = marc.matnr and resb.mandt = marc.mandt
        inner join mara on marc.mandt = mara.mandt and marc.matnr = mara.matnr
        inner join t024d on marc.dispo = t024d.dispo and t024d.mandt = marc.mandt
        inner join t457t on resb.bdart = t457t.delkz and resb.mandt = t457t.mandt
        where sales_order_no like any ($salesOrdersWhereClause)
        )
        select * from final order by sales_order_item_number;
    ";

    $snowflakeQueryObject = new SnowflakeQuery("ONEX", "PRD_DISTRIBUTE", "ERP_RAW_AP001", $spiridon001SnowflakeQuery, [], false, ["batchSize" => 15]);

    try {
        if (!executeAndFetchResults($snowflakeQueryObject)) {
            // Handle fatal error
            throw new Exception("Failed to execute Snowflake query");
        }
    } catch (Exception $e) {
        handleQueryFailure($e->getMessage());
        exit(1); // Stop further execution
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
            $PROD_ORDER_NO = htmlspecialchars($row["PROD_ORDER_NO"]);
            $PLANT = htmlspecialchars($row["PLANT"]);
            $SALES_ORDER_NO = htmlspecialchars($row["SALES_ORDER_NO"]);
            $MATERIAL = htmlspecialchars($row["MATERIAL"]);
            $ITEM_NO = htmlspecialchars($row["ITEM_NO"]);
            $SALES_ORDER_ITEM_NUMBER = htmlspecialchars($row["SALES_ORDER_ITEM_NUMBER"]);
            $REQUIREMENT_QUANTITY = htmlspecialchars($row["REQUIREMENT_QUANTITY"]);
            $WITHDRAWN_QUANTITY = htmlspecialchars($row["WITHDRAWN_QUANTITY"]);
            $RESB_STORAGE_LOCATION = htmlspecialchars($row["RESB_STORAGE_LOCATION"]);
            $ITEM_CATEGORY = htmlspecialchars($row["ITEM_CATEGORY"]);
            $BACKFLUSH_INDICATOR = htmlspecialchars($row["BACKFLUSH_INDICATOR"]);
            $HIGHER_BOM = htmlspecialchars($row["HIGHER_BOM"]);
            $SPECIAL_STOCK = htmlspecialchars($row["SPECIAL_STOCK"]);
            $BASE_UNIT = htmlspecialchars($row["BASE_UNIT"]);
            $MATERIAL_DESC = htmlspecialchars($row["MATERIAL_DESC"]);
            $MRP_CONTROLLER = htmlspecialchars($row["MRP_CONTROLLER"]);
            $CONTROLLER_NAME = htmlspecialchars($row["CONTROLLER_NAME"]);
            $MATERIAL_TYPE = htmlspecialchars($row["MATERIAL_TYPE"]);
            $KMAT_NUMBER = htmlspecialchars($row["KMAT_NUMBER"]);
            $KMAT_NAME = htmlspecialchars($row["KMAT_NAME"]);
            $DELETION_IND = htmlspecialchars($row["DELETION_IND"]);
            $STLNR = htmlspecialchars($row["STLNR"]);
            $T4TYPE = htmlspecialchars($row["T4TYPE"]);
            $MAINTYPE = htmlspecialchars($row["MAINTYPE"]);
            $SEQUENCE_ORDER_NUMBER = htmlspecialchars($row["SEQUENCE_ORDER_NUMBER"]);
            $PROFIT_CENTER = htmlspecialchars($row["PROFIT_CENTER"]);
            $REQUIREMENT_TYPE = htmlspecialchars($row["REQUIREMENT_TYPE"]);
            $PHANTOM_ITEM_INDICATOR = htmlspecialchars($row["PHANTOM_ITEM_INDICATOR"]);
            $INTRA_MATERIAL_INDICATOR = htmlspecialchars($row["INTRA_MATERIAL_INDICATOR"]);
            $FINAL_ISSUE_RESERVATION = htmlspecialchars($row["FINAL_ISSUE_RESERVATION"]);
            $BOM_ITEM_TEXT = htmlspecialchars($row["BOM_ITEM_TEXT"]);
            $SUPPLY_AREA = htmlspecialchars($row["SUPPLY_AREA"]);
            $RECORD_TYPE = htmlspecialchars($row["RECORD_TYPE"]);
            $RESERVATION_NUMBER = htmlspecialchars($row["RESERVATION_NUMBER"]);
            $RESERVATION_ITEM_NUMBER = htmlspecialchars($row["RESERVATION_ITEM_NUMBER"]);
            $LATEST_REQUIREMENT_DATE = htmlspecialchars($row["LATEST_REQUIREMENT_DATE"]);
            $BULK_MATERIAL_INDICATOR = htmlspecialchars($row["BULK_MATERIAL_INDICATOR"]);
            $ITEM_TEXT_INDICATOR = htmlspecialchars($row["ITEM_TEXT_INDICATOR"]);
            $MISSING_PART = htmlspecialchars($row["MISSING_PART"]);
            $ITEM_IS_DELETED = htmlspecialchars($row["ITEM_IS_DELETED"]);
            $GOODS_MOVEMENT_FOR_RESERVATION = htmlspecialchars($row["GOODS_MOVEMENT_FOR_RESERVATION"]);
            $MRP_CONTROLLER = htmlspecialchars($row["MRP_CONTROLLER"]);
            $LANGUAGE_KEY = htmlspecialchars($row["LANGUAGE_KEY"]);
            $MATERIAL_GROUP = htmlspecialchars($row["MATERIAL_GROUP"]);
            $MATERIAL_GROUPING = htmlspecialchars($row["MATERIAL_GROUPING"]);
            $BOM_ITEM_NUMBER = htmlspecialchars($row["BOM_ITEM_NUMBER"]);

            if (str_ends_with($REQUIREMENT_QUANTITY, '.000')) $REQUIREMENT_QUANTITY = preg_replace('/\.000$/', '', $REQUIREMENT_QUANTITY);

            if ($BASE_UNIT === "ST") $BASE_UNIT = "PC";

            $materialDescKey = ":$insertCount" . "materialDesc";
            $insertToTempQueryParameters[] = [$materialDescKey => htmlspecialchars($MATERIAL_DESC)];

            $insertToTempQuery .= "(
                '$PROD_ORDER_NO',
                '$SEQUENCE_ORDER_NUMBER',
                '$SALES_ORDER_NO',
                '$SALES_ORDER_ITEM_NUMBER',
                '$PROFIT_CENTER',
                '$PLANT',
                '$HIGHER_BOM',
                '$REQUIREMENT_TYPE',
                '$REQUIREMENT_QUANTITY',
                '$BASE_UNIT',
                '$DATE_OF_REQUIREMENT',
                '$PHANTOM_ITEM_INDICATOR',
                '$WITHDRAWN_QUANTITY',
                '', -- Bun2
                '$INTRA_MATERIAL_INDICATOR',
                '$FINAL_ISSUE_RESERVATION',
                '$RESB_STORAGE_LOCATION',
                '$MATERIAL',
                '$ITEM_CATEGORY',
                '$BOM_ITEM_TEXT',
                '$SUPPLY_AREA',
                '$BACKFLUSH_INDICATOR',
                '$RECORD_TYPE',
                '$RESERVATION_NUMBER',
                '$RESERVATION_ITEM_NUMBER',
                '$LATEST_REQUIREMENT_DATE', 
                '$BULK_MATERIAL_INDICATOR', 
                '$SPECIAL_STOCK', 
                '$ITEM_TEXT_INDICATOR', 
                '$MISSING_PART', 
                '$ITEM_IS_DELETED', 
                '$GOODS_MOVEMENT_FOR_RESERVATION', 
                '$MRP_CONTROLLER', 
                '$LANGUAGE_KEY', 
                '$MATERIAL_GROUP', 
                '$MATERIAL_GROUPING', 
                '$BOM_ITEM_NUMBER', 
                '$MATERIAL_TYPE', 
                '$KMAT_NUMBER', 
                '$KMAT_NAME', 
                $materialDescKey 
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
    ADD INDEX ix_sap_spiridon_001_temp_StorageLocation (StorageLocation) USING BTREE, 
    ADD INDEX ix_sap_spiridon_001_temp_RecordType (RecordType) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_BomItemNumber (BomItemNumber) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_QuantityWithdrawn (QuantityWithdrawn) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_OrderNumber (OrderNumber) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_SeqNumberOrder (SeqNumberOrder) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_Plant (Plant) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ReqQuantity (ReqQuantity) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_BUn (BUn) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_Typ (Typ) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_MissingPart (MissingPart) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_IndicatorBulkMaterial (IndicatorBulkMaterial) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ItemTextIndicator (ItemTextIndicator) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_PhantomItemIndicator (PhantomItemIndicator) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_SupplyArea (SupplyArea) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_SalesOrder (SalesOrder) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_FinalIssueOfReser (FinalIssueOfReser) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_LatestReqDate (LatestReqDate) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_MaterialGroup (MaterialGroup) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_MaterialGrouping (MaterialGrouping) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ItemNumberOfReservation (ItemNumberOfReservation) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_MaterialDescription (MaterialDescription) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_BomItemText (BomItemText) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_NumberOfReservation (NumberOfReservation) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ReqDate (ReqDate) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ItemCategory (ItemCategory) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_MRP (MRP) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_MaterialNumber (MaterialNumber) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_SpecialStockIndicator (SpecialStockIndicator) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_LanguageKey (LanguageKey) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_GoodsMovementForReservation (GoodsMovementForReservation) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_IndicatorIntraMaterial (IndicatorIntraMaterial) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_BUn2 (BUn2) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ProfitCenter (ProfitCenter) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ReqType (ReqType) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ItemIsDeleted (ItemIsDeleted) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_MaterialNumber2 (MaterialNumber2) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_ItemNumber (ItemNumber) USING BTREE,
    ADD INDEX ix_sap_spiridon_001_temp_IndicatorBackflush (IndicatorBackflush) USING BTREE";
CronDbManager::fetchQuery('rpa', $addTempTableKeysQuery);

$getPrevBackupTablesQuery = "SELECT TABLE_NAME FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = 'rpa' 
    AND TABLE_NAME LIKE 'sap_spiridon_001_%' 
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
    // Implement logic to handle query failures
    echo "Query failed with status: $status";
    // $mailSubject = 'sap_spiridon_001 cron error';
    // $htmlContent = "Query failed with status: $status";
    // $email = ['swapnil.takke.ext@siemens.com'];
    // MailManager::sendMail($mailSubject, $htmlContent, 'new_user', [], $email);
    // Additional logging or error handling can be implemented here
}
?>