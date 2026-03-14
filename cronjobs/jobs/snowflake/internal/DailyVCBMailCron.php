<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";
header('Content-Type:application/json;charset=utf-8;');

$email = [
    'avinash.kesarkar@siemens.com',
    'vinol.dsilva@siemens.com',
    'omkar.kulkarni@siemens.com',
    'vikrant.phanase@siemens.com',
    'mandar.shinde@siemens.com',
    'swapnil.takke@siemens.com',
    'suryawanshi.akshay@siemens.com',
    'anagha.kamble@siemens.com',
    'paavan.sarna@siemens.com',
    'akshay.ingle@siemens.com',
    'pradnya.rangdal@siemens.com'
];

$cc_email = [
    'radhey.barnwal@siemens.com',
    'salilkumar.pampattiwar@siemens.com',
    'yogendra.savaikar@siemens.com',
    'Shirish.Waghode@siemens.com',
    'lalitnarayan.bagayatkar@siemens.com',
    'parikshit.kadam@siemens.com'
];

$currentDay = date('w');
if ($currentDay == 0) {
    return;
} elseif ($currentDay == 1) {
    $display_date = date('Y-m-d', strtotime('-2 day'));

    $start_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime($start_date . ' -2 days'));
    $start_date = $start_date." 00:00:00";

    $end_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime($end_date . ' -2 days'));
    $end_date = $end_date." 23:59:59";
} else {
    $display_date = date('Y-m-d', strtotime('-1 day'));

    $start_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime($start_date . ' -1 days'));
    $start_date = $start_date." 00:00:00";

    $end_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime($end_date . ' -1 days'));
    $end_date = $end_date." 23:59:59";
}

$sql_mail = "SELECT 
    station_name,
    COUNT(DISTINCT barcode) AS total_product_count,
    COUNT(DISTINCT CASE 
        WHEN TIME(up_date) BETWEEN '06:15:00' AND '14:45:00' THEN barcode 
        END) AS shift_1_product_count,
    COUNT(DISTINCT CASE 
        WHEN TIME(up_date) BETWEEN '14:45:01' AND '23:59:59' THEN barcode 
        END) AS shift_2_product_count,
    DATE(MIN(up_date)) AS start_datetime, 
    DATE(MAX(up_date)) AS end_datetime, 
    up_date AS sort_date
FROM (
    SELECT 
        tr_id, 
        barcode, 
        stage_id, 
        station_name, 
        user_id, 
        product_name, 
        up_date,
        ROW_NUMBER() OVER (PARTITION BY barcode, station_name, user_id ORDER BY stage_id DESC) AS rn
    FROM tbl_transactions
    WHERE up_date BETWEEN :start_date AND :end_date
    AND (station_name <> 'Stamping' OR (station_name = 'Stamping' AND STATUS = '1'))
) AS subquery
GROUP BY 
    station_name
UNION ALL
SELECT
    station_name,
    COUNT(DISTINCT barcode) AS total_product_count,
    (SELECT COUNT(DISTINCT barcode)
    FROM (
        SELECT barcode
        FROM tbl_warehousebarcode
        WHERE station_name = 'Warehouse'
        AND TIME(up_date) BETWEEN '06:15:00' AND '14:45:00'
        AND up_date BETWEEN :start_date AND :end_date
        GROUP BY barcode
    ) AS shift_1_barcodes) AS shift_1_product_count,
    (SELECT COUNT(DISTINCT barcode)
    FROM (
        SELECT barcode
        FROM tbl_warehousebarcode
        WHERE station_name = 'Warehouse'
        AND TIME(up_date) BETWEEN '14:45:01' AND '23:59:59'
        AND up_date BETWEEN :start_date AND :end_date
        AND barcode NOT IN (
            SELECT barcode
            FROM tbl_warehousebarcode
            WHERE station_name = t.station_name
            AND TIME(up_date) BETWEEN '06:15:00' AND '14:45:00'
            AND up_date BETWEEN :start_date AND :end_date
        )
    ) AS shift_2_barcodes) AS shift_2_product_count,
    DATE(up_date) AS start_datetime,
    DATE(up_date) AS end_datetime,
    up_date AS sort_date
    FROM
    tbl_warehousebarcode t
    WHERE
    up_date BETWEEN :start_date AND :end_date
    AND (station_name <> 'Stamping' OR (station_name = 'Stamping' AND STATUS = '1'))
    GROUP BY
    station_name";

$result = DbManager::fetchPDOQueryData('spectra_db', $sql_mail, [":start_date" => "$start_date", ":end_date" => "$end_date"])["data"];

$sql_breaker_mail = "SELECT product_name, COUNT(*) AS COUNT FROM tbl_breaker_details WHERE create_date BETWEEN :start_date AND :end_date GROUP BY product_name";
$result_breaker = DbManager::fetchPDOQueryData('spectra_db', $sql_breaker_mail, [":start_date" => "$start_date", ":end_date" => "$end_date"])["data"];

$htmlContent = "<p>Hello All,</p><br><p>Manufacturing output from SION M VCB manufacturing line for  " . $display_date . "</p>";
$htmlContent .= "<table border='1' cellpadding='5'><tr>";
$htmlContent .= "<th>Station Name</th><th>1st Shift</th><th>2nd Shift</th><th>Total</th>";
$htmlContent .= "</tr>";

foreach ($result as $row) {
    $htmlContent .= "<tr>";
    $htmlContent .= "<td>" . htmlspecialchars($row['station_name']) . "</td>";
    $htmlContent .= "<td>" . htmlspecialchars($row['shift_1_product_count']) . "</td>";
    $htmlContent .= "<td>" . htmlspecialchars($row['shift_2_product_count']) . "</td>";
    $htmlContent .= "<td>" . htmlspecialchars($row['total_product_count']) . "</td>";
    $htmlContent .= "</tr>";
}

$htmlContent .= "</table>";

$htmlContent .= "<br><p>New Breakers registered at planning stage:</p>";

$htmlContent .= "<table border='1' cellpadding='5'><tr>";
$htmlContent .= "<th>Product Name</th><th>Total</th>";
$htmlContent .= "</tr>";

if (empty($result_breaker)) {
    $htmlContent .= "<tr><td>NIL</td><td>NIL</td></tr>";
} else {
    foreach ($result_breaker as $row) {
        $htmlContent .= "<tr>";
        $htmlContent .= "<td>" . htmlspecialchars($row['product_name']) . "</td>";
        $htmlContent .= "<td>" . htmlspecialchars($row['COUNT']) . "</td>";
        $htmlContent .= "</tr>";
    }
}

$htmlContent .= "</table>";

$htmlContent .= "<p>You can access detailed report in OneX from the https://onex.siemens.co.in/dpm/report_viewer.php address by using the Microsoft Edge / Google Chrome browser.
<br><br><br>
With Best Regards,
<br>
SI EA O AIS THA
</p>";

sendNotificationEmail($email, $htmlContent, $cc_email);

function sendNotificationEmail($emails, $htmlContent, $cc_email) {
    $SELF_ORG_CODE = SharedManager::getFromSharedEnv('SELF_ORG_CODE');
    $mailSubject = 'SION M : VCB Manufacturing Daily Report';
    MailManager::sendMail($mailSubject, $htmlContent, 'new_user', [], $emails, $cc_email);
}
?>