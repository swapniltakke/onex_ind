<?php

/*********************************
 LOAD MANAGER
*********************************/

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);


/*********************************
 DEBUG FUNCTION
*********************************/
function debug($title, $data = null)
{
    echo "<hr>";
    echo "<b>$title</b><br>";

    if ($data !== null) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}


/*********************************
 STEP 1: FETCH SNAPSHOTS
*********************************/

debug("STEP 1: Fetching machine_snapshot_update");

$sql = "
SELECT job_id, datapoints
FROM machine_snapshot_update
WHERE job_id IS NOT NULL
  AND job_id != ''
  AND datapoints IS NOT NULL
  AND datapoints != ''
ORDER BY id DESC
";

$snapshots = DbManager::fetchPDOQueryData(
    'spectra_db',
    $sql,
    []
)['data'] ?? [];

debug("Snapshots Found", count($snapshots));

if (empty($snapshots)) {
    die("No snapshots found.");
}


/*********************************
 STEP 2: GROUP BY TIMESTAMP
*********************************/

debug("STEP 2: Grouping by timestamp");

$groups = [];

foreach ($snapshots as $row) {

    if (preg_match('/_(\d{14})_/', $row['job_id'], $m)) {

        $timeKey = $m[1];

        $groups[$timeKey][] = [
            'job_id'     => $row['job_id'],
            'datapoints' => $row['datapoints']
        ];
    }
}

debug("Groups", array_keys($groups));


/*********************************
 HELPER: PARSE DATAPOINTS
*********************************/

function parseDatapoints($str)
{
    $result = [];

    $pairs = explode(',', $str);

    foreach ($pairs as $pair) {

        if (strpos($pair, ':') !== false) {

            [$k, $v] = explode(':', $pair, 2);

            $key = trim($k);
            $val = trim($v);

            if (is_numeric($val)) {
                $val = (float)$val;
            }

            $result[$key] = $val;
        }
    }

    return $result;
}


/*********************************
 STEP 3: PROCESS GROUPS
*********************************/

debug("STEP 3: Processing Groups");

$insertCount = 0;


foreach ($groups as $time => $rows) {

    debug("Processing Group", $time);


    /*********************************
     MERGE DATA
    *********************************/

    $finalData = [];
    $jobId     = '';

    foreach ($rows as $rowData) {

        $jobId = $rowData['job_id'];

        $parsed = parseDatapoints($rowData['datapoints']);

        foreach ($parsed as $k => $v) {

            if (!isset($finalData[$k])) {

                $finalData[$k] = $v;

            } else {

                if (is_numeric($v) && $v > $finalData[$k]) {

                    $finalData[$k] = $v;
                }
            }
        }
    }

    debug("Merged Data", $finalData);


    /*********************************
     STEP 4: VALIDATE REQUIRED TAGS
    *********************************/

    $requiredTags = [
        'Data_block_2.VCB',
        'Data_block_2.SERIAL[0]',
        'Data_block_2.M_BARCODE[0]',
        'mV DROP LIMIT',
        'mV ACT'
    ];

    $missing = [];

    foreach ($requiredTags as $tag) {

        if (!isset($finalData[$tag]) || $finalData[$tag] === '') {
            $missing[] = $tag;
        }
    }

    if (!empty($missing)) {

        debug("SKIPPED (Missing Tags)", $missing);
        continue;
    }


    /*********************************
     STEP 5: DETECT STATION
    *********************************/

    $stationName = '';
    $stationId   = 0;

    if (stripos($jobId, 'mV DROP ST-1') !== false) {

        $stationName = 'mV DROP ST-1';
        $stationId   = 20;

    } elseif (stripos($jobId, 'mV DROP ST-2') !== false) {

        $stationName = 'mV DROP ST-2';
        $stationId   = 21;

    } else {

        debug("SKIPPED (Unknown Station)", $jobId);
        continue;
    }

    debug("Detected Station", $stationName);


    /*********************************
     STEP 6: FINAL OUTPUT STRING
    *********************************/

    $finalArr = [];

    foreach ($finalData as $k => $v) {
        $finalArr[] = "$k: $v";
    }

    $finalOutput = implode(', ', $finalArr);


    /*********************************
     STEP 7: DUPLICATE CHECK
    *********************************/

    $serial  = trim($finalData['Data_block_2.SERIAL[0]']);
    $barcode = trim($finalData['Data_block_2.M_BARCODE[0]']);

    $checkSql = "
    SELECT 1
    FROM tbl_transactions
    WHERE serial_no = :serial
      AND barcode = :barcode
      AND station_id = :station_id
    LIMIT 1
    ";

    $check = DbManager::fetchPDOQueryData(
        'spectra_db',
        $checkSql,
        [
            ':serial'     => $serial,
            ':barcode'    => $barcode,
            ':station_id' => $stationId
        ]
    )['data'] ?? [];

    if (!empty($check)) {

        debug("SKIPPED (Duplicate)", [$serial, $barcode]);
        continue;
    }


    /*********************************
     STEP 8: FETCH OLD PRODUCT DATA
    *********************************/

    $productId    = null;
    $productName  = null;
    $finalBarcode = $barcode;


    $fetchSql = "
    SELECT product_id, product_name, barcode
    FROM tbl_transactions
    WHERE barcode LIKE :serial_like
    ORDER BY tr_id DESC
    LIMIT 1
    ";

    $fetch = DbManager::fetchPDOQueryData(
        'spectra_db',
        $fetchSql,
        [
            ':serial_like' => '%' . $serial . '%'
        ]
    )['data'] ?? [];


    if (!empty($fetch)) {

        $productId    = $fetch[0]['product_id'];
        $productName  = $fetch[0]['product_name'];
        $finalBarcode = $fetch[0]['barcode'];

        debug("Previous Record Found", $fetch[0]);

    } else {

        debug("No Previous Record", $serial);
    }


    /*********************************
     STEP 9: INSERT DATA
    *********************************/

    $insertSql = "
    INSERT INTO tbl_transactions
    (
        product_id,
        product_name,
        user_id,
        cust_name,
        serial_no,
        panel_no,
        station_id,
        station_name,
        start_time,
        end_date,
        barcode,
        stage_id,
        actual_output,
        remarks,
        status,
        up_date
    )
    VALUES
    (
        :product_id,
        :product_name,
        :user_id,
        NULL,
        :serial,
        NULL,
        :station_id,
        :station_name,
        NULL,
        NULL,
        :barcode,
        :stage_id,
        :output,
        :remarks,
        :status,
        NOW()
    )
    ";


    $params = [
        ':product_id'   => $productId,
        ':product_name' => $productName,
        ':user_id'      => 'PLC User',
        ':serial'       => $serial,
        ':station_id'   => $stationId,
        ':station_name' => $stationName,
        ':barcode'      => $finalBarcode,
        ':stage_id'     => 7,
        ':output'       => $finalOutput,
        ':remarks'      => 'Auto inserted from snapshot',
        ':status'       => 1
    ];


    $result = DbManager::fetchPDOQueryData(
        'spectra_db',
        $insertSql,
        $params
    );


    if ($result !== false) {

        $insertCount++;

        debug("INSERTED SUCCESS", $params);

    } else {

        debug("INSERT FAILED", $params);
    }
}


/*********************************
 FINISH
*********************************/

echo "<hr>";
echo "<h2>PROCESS FINISHED</h2>";
echo "<h3>Total Inserted: $insertCount</h3>";
echo "<hr>";

