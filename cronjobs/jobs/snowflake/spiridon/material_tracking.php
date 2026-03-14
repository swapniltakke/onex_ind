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
    $maxRetries = 30; // 150 seconds total wait time (30 * 5s)
    // SharedManager::print($queryObject);
    // while ($queryObject->nextPartition()) {
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
    // }
    return true;
}

// Define constants
$TABLE_NAME = "sap_material_tracking";
$TEMP_TABLE_NAME = "{$TABLE_NAME}_temp";
$now = date("Ymd_His");
$BACKUP_TABLE_NAME = "{$TABLE_NAME}_{$now}";
$BATCH_SIZE = 15000;

// Create temporary table
$materialTrackingTableContents = "
    `id` INT NOT NULL AUTO_INCREMENT,
    `tracking_number` VARCHAR(50) NULL DEFAULT NULL,
    `sales_order_no` VARCHAR(50) NULL DEFAULT NULL,
    `production_order_no` VARCHAR(50) NULL DEFAULT NULL,
    `purchase_order_no` VARCHAR(50) NULL DEFAULT NULL,
    `purchase_item` VARCHAR(50) NULL DEFAULT NULL,
    `vendor_name` VARCHAR(100) NULL DEFAULT NULL,
    `vendor_code` VARCHAR(50) NULL DEFAULT NULL,
    `po_created_by` VARCHAR(50) NULL DEFAULT NULL,
    `our_reference` VARCHAR(250) NULL DEFAULT NULL,
    `po_date` DATETIME NULL DEFAULT NULL,
    `material` VARCHAR(50) NULL DEFAULT NULL,
    `short_text` TEXT NULL DEFAULT NULL,
    `po_quantity` DECIMAL(10,2) NULL DEFAULT NULL,
    `quantity_delivered` DECIMAL(10,2) NULL DEFAULT NULL,
    `still_to_be_delivered` DECIMAL(10,2) NULL DEFAULT NULL,
    `delivery_date` DATETIME NULL DEFAULT NULL,
    `delivery_date_vendor_confirmation` DATETIME NULL DEFAULT NULL,
    `account_assignment_category` VARCHAR(50) NULL DEFAULT NULL,
    `net_value_po_currency` DECIMAL(10,2) NULL DEFAULT NULL,
    `currency` VARCHAR(10) NULL DEFAULT NULL,
    `purch_grp` VARCHAR(50) NULL DEFAULT NULL,
    `deletion_ind` VARCHAR(50) NULL DEFAULT NULL,
    `storage_no` VARCHAR(50) NULL DEFAULT NULL,
    `storage_location_desc` VARCHAR(100) NULL DEFAULT NULL,
    `Created` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    `Updated` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
";

// Create temporary table
$createTempTableQuery = "
    CREATE TABLE IF NOT EXISTS `$TEMP_TABLE_NAME` (
        $materialTrackingTableContents
    );
";
CronDbManager::fetchQuery('rpa', $createTempTableQuery);

// Truncate temporary table
$truncateTempTableQuery = "TRUNCATE $TEMP_TABLE_NAME";
CronDbManager::fetchQuery('rpa', $truncateTempTableQuery);

// Create main table if not exists
$createTableQuery = "
    CREATE TABLE IF NOT EXISTS `$TABLE_NAME` (
        $materialTrackingTableContents
    );
";
CronDbManager::fetchQuery('rpa', $createTableQuery);

// Get sales orders and process in chunks
$salesOrders = getProjects();
$salesOrdersChunks = array_chunk($salesOrders, 10);

foreach ($salesOrdersChunks as $chunk) {
    sleep(15);
    $salesOrdersString = "'" . implode("','", $chunk) . "'";
    
    $materialTrackingQuery = "
        with 
            temp as (
                select
                    ekpo.mandt as mandt,
                    ekpo.bednr as tracking_number,
                    afpo.kdauf as sales_order_no,
                    ltrim(ekkn.aufnr, '0') as production_order_no,
                    ekpo.ebeln as purchase_order_no,
                    ekpo.ebelp as purchase_item,
                    lfa1.name1 as vendor_name,
                    ltrim(ekko.lifnr, '0') as vendor_code,
                    ekko.ernam as po_created_by,
                    ekko.unsez as our_reference,
                    ekpo.creationdate as po_date,
                    ltrim(ekpo.matnr, '0') as material,
                    ekpo.txz01 as short_text,
                    ekpo.menge as po_quantity,
                    SUM(CASE
                        WHEN mseg.bwart = '101' THEN mseg.menge -- Standard Goods Receipt
                        WHEN mseg.bwart = '102' THEN -mseg.menge -- Reversal of Goods Receipt
                        ELSE 0
                    END) AS quantity_delivered,
                    po_quantity - quantity_delivered as still_to_be_delivered,
                    ekpo.knttp as account_assignment_category,
                    ekpo.netwr as net_value_po_currency,
                    ekko.waers as currency,
                    ekpo.loekz as deletion_ind,
                    ekko.ekgrp as purch_grp,
                    ekpo.lgort as storage_no,
                    t001l.lgobe as storage_location_desc
                from ekpo 
                join ekko on ekpo.mandt = ekko.mandt and ekpo.ebeln = ekko.ebeln
                join lfa1 on ekko.mandt = lfa1.mandt and ekko.lifnr = lfa1.lifnr
                left join ekkn on ekpo.mandt = ekkn.mandt and ekpo.ebeln = ekkn.ebeln and ekpo.ebelp = ekkn.ebelp
                left join afpo on ekkn.mandt = afpo.mandt and ekkn.aufnr = afpo.aufnr
                left join mseg on ekpo.mandt = mseg.mandt and ekpo.ebeln = mseg.ebeln and ekpo.ebelp = mseg.ebelp
                left join t001l on ekpo.lgort = t001l.lgort and t001l.werks = '9E62'
                where ekpo.werks = '9E62' and ekpo.mandt = '100'
                group by all    
            ),
        
            final as (
                select 
                    temp.*,
                    max(eket.eindt) as delivery_date,
                    max(ekes.eindt) as delivery_date_vendor_confirmation
                from temp
                left join eket on temp.mandt = eket.mandt and temp.purchase_order_no = eket.ebeln and temp.purchase_item = eket.ebelp
                left join ekes on temp.mandt = ekes.mandt and temp.purchase_order_no = ekes.ebeln and temp.purchase_item = ekes.ebelp and ekes.ebtyp = 'AB'
                group by all
            )
        
        select * exclude(mandt) from final where tracking_number IN ($salesOrdersString) order by purchase_order_no, purchase_item;
    ";

    $snowflakeQueryObject = new SnowflakeQuery("ONEX", "PRD_DISTRIBUTE", "ERP_RAW_AP001", $materialTrackingQuery, [], false, ["batchSize" => 15]);
    // SharedManager::print($snowflakeQueryObject);
    // exit;
    try {
        if (!executeAndFetchResults($snowflakeQueryObject)) {
            throw new Exception("Failed to execute Snowflake query");
        }
    } catch (Exception $e) {
        handleQueryFailure($e->getMessage());
        exit(1);
    }

    // Process temporary files and insert into database
    $tempDataPath = getTempDataFileSavePath();
    $folderContent = scandir($tempDataPath);
    if ($folderContent === false) {
        returnHttpResponse(500, "Path: '$tempDataPath' does not exists ");
    }
    // SharedManager::print($folderContent);
    // SharedManager::print($file);
    $tempDataFiles = array_filter($folderContent, function ($file) use ($TABLE_NAME) {
        return str_starts_with($file, $TABLE_NAME) && !in_array($file, [".", ".."]);
    });

    foreach ($tempDataFiles as $tempDataFile) {
        $tempDataFilePath = "$tempDataPath/$tempDataFile";
        $fileContent = file_get_contents($tempDataFilePath);
        $data = json_decode($fileContent, true);
        unset($fileContent);

        $columnsList = "tracking_number, sales_order_no, production_order_no, purchase_order_no,
            purchase_item, vendor_name, vendor_code, po_created_by, our_reference, po_date,
            material, short_text, po_quantity, quantity_delivered,
            still_to_be_delivered, delivery_date, delivery_date_vendor_confirmation,
            account_assignment_category, net_value_po_currency, 
            currency, purch_grp, deletion_ind, storage_no, storage_location_desc";

        $insertQuery = "INSERT INTO $TEMP_TABLE_NAME ($columnsList) VALUES ";

        $values = [];
        $insertCount = 0;
        $totalRows = count($data);

        foreach ($data as $row) {
            $values[] = "(
                '" . htmlspecialchars($row['TRACKING_NUMBER']) . "',
                '" . htmlspecialchars($row['SALES_ORDER_NO']) . "',
                '" . htmlspecialchars($row['PRODUCTION_ORDER_NO']) . "',
                '" . htmlspecialchars($row['PURCHASE_ORDER_NO']) . "',
                '" . htmlspecialchars($row['PURCHASE_ITEM']) . "',
                '" . htmlspecialchars($row['VENDOR_NAME']) . "',
                '" . htmlspecialchars($row['VENDOR_CODE']) . "',
                '" . htmlspecialchars($row['PO_CREATED_BY']) . "',
                '" . htmlspecialchars($row['OUR_REFERENCE']) . "',
                '" . htmlspecialchars($row['PO_DATE']) . "',
                '" . htmlspecialchars($row['MATERIAL']) . "',
                '" . htmlspecialchars($row['SHORT_TEXT']) . "',
                " . floatval($row['PO_QUANTITY']) . ",
                " . floatval($row['QUANTITY_DELIVERED']) . ",
                " . floatval($row['STILL_TO_BE_DELIVERED']) . ",
                '" . htmlspecialchars($row['DELIVERY_DATE']) . "',
                '" . htmlspecialchars($row['DELIVERY_DATE_VENDOR_CONFIRMATION']) . "',
                '" . htmlspecialchars($row['ACCOUNT_ASSIGNMENT_CATEGORY']) . "',
                " . floatval($row['NET_VALUE_PO_CURRENCY']) . ",
                '" . htmlspecialchars($row['CURRENCY']) . "',
                '" . htmlspecialchars($row['PURCH_GRP']) . "',
                '" . htmlspecialchars($row['DELETION_IND']) . "',
                '" . htmlspecialchars($row['STORAGE_NO']) . "',
                '" . htmlspecialchars($row['STORAGE_LOCATION_DESC']) . "'
            )";

            $insertCount++;

            if ($insertCount === $BATCH_SIZE) {
                $batchInsertQuery = $insertQuery . implode(",", $values);
                CronDbManager::fetchQuery('rpa', $batchInsertQuery);
                $values = [];
                $insertCount = 0;
                $insertQuery = "INSERT INTO $TEMP_TABLE_NAME ($columnsList) VALUES ";
            }
        }

        if (!empty($values)) {
            $batchInsertQuery = $insertQuery . implode(",", $values);
            CronDbManager::fetchQuery('rpa', $batchInsertQuery);
        }

        unlink($tempDataFilePath);
    }
}

// Add indexes to temporary table
$addIndexesQuery = "ALTER TABLE $TEMP_TABLE_NAME 
    ADD INDEX ix_tracking_number (tracking_number),
    ADD INDEX ix_sales_order_no (sales_order_no),
    ADD INDEX ix_production_order_no (production_order_no),
    ADD INDEX ix_purchase_order_no (purchase_order_no)";
CronDbManager::fetchQuery('rpa', $addIndexesQuery);

// Handle table rotation
$getPrevBackupTablesQuery = "SELECT TABLE_NAME FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = 'rpa' 
    AND TABLE_NAME LIKE 'sap_material_tracking_%' 
    AND TABLE_NAME NOT IN('$TEMP_TABLE_NAME', '$BACKUP_TABLE_NAME')";

$prevBackupTables = CronDbManager::fetchQueryData('rpa', $getPrevBackupTablesQuery)["data"];
foreach ($prevBackupTables as $row) {
    $prevBackupTable = $row["TABLE_NAME"];
    $dropPrevBackupTableQuery = "DROP TABLE IF EXISTS $prevBackupTable";
    CronDbManager::fetchQuery('rpa', $dropPrevBackupTableQuery);
}

// Rename tables
$renameTablesQuery = "RENAME TABLE $TABLE_NAME TO $BACKUP_TABLE_NAME, $TEMP_TABLE_NAME TO $TABLE_NAME;";
CronDbManager::fetchQuery('rpa', $renameTablesQuery);

echo "Process completed successfully!\n";

function handleQueryFailure($status) {
    error_log("Snowflake query failed: $status");
    // Consider sending an alert or notification
}

?>