<!DOCTYPE html>
<html>
<?php
include_once 'core/index.php';
$shift1_start = '06:15:00'; // Start time for Shift 1
$shift1_end = '14:45:00';   // End time for Shift 1
$shift2_start = '14:45:01'; // Start time for Shift 2
$shift2_end = '23:59:59';   // End time for Shift 2
$current_date = date('Y-m-d');
$first_day_of_month = date('Y-m-01');
$shift1_count = 0;
$shift2_count = 0;
$current_date_from =  date('Y-m-d');
// $current_date_from =  date('Y-m-d', strtotime($current_date . ' -1 days'));
$current_date_from = $current_date_from . " 00:00:00";
$current_date_to =  date('Y-m-d');
// $current_date_to =  date('Y-m-d', strtotime($current_date . ' -1 days'));
$current_date_to = $current_date_to . " 23:59:59";
$stage_ids = [7];

$sql_shiftwise = "SELECT CASE 
                        WHEN p.product_name IN ('SION M25', 'SION M31') THEN 'SION M25/31'
                        WHEN p.product_name IN ('SION M40', 'SION M31+') THEN 'SION M40/31+'
                        ELSE p.product_name
                    END AS product_name,
                    s.station_name,
                    t.stage_id,
                    COUNT(CASE
                            WHEN TIME(t.up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                            END) AS shift1_count,
                    COUNT(CASE
                            WHEN TIME(t.up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                            END) AS shift2_count,
                    COUNT(CASE
                            WHEN t.up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1
                            END) AS mtd_count
                FROM   tbl_transactions t
                    JOIN tbl_product p
                        ON t.product_id = p.product_id
                    JOIN tbl_station s
                        ON t.station_id = s.station_id
                WHERE t.stage_id IN (:stage_id)
                    AND t.station_name = :station_name
                    AND t.up_date BETWEEN :current_date_from AND :current_date_to
                GROUP  BY p.product_name,
                        s.station_name,
                        t.stage_id
                ORDER  BY t.stage_id";

$params = [
    ":shift1_start" => $shift1_start,
    ":shift1_end" => $shift1_end,
    ":shift2_start" => $shift2_start,
    ":shift2_end" => $shift2_end,
    ":stage_id" => "7",
    ":current_date_from" => $current_date_from,
    ":current_date_to" => $current_date_to,
    ":station_name" => "A21 (Dropping)"
];

$result_shiftwise_data = DbManager::fetchPDOQueryData('spectra_db', $sql_shiftwise, $params)["data"];
// SharedManager::print($result_shiftwise_data);

$currentTime = date('H:i:s'); // Get the current time
if (!empty($result_shiftwise_data)) {
    foreach ($result_shiftwise_data as $product) {
        if ($product['product_name'] == 'SION M25/31') {
            if ($currentTime >= '06:15:00' && $currentTime <= '14:45:00') {
                $current_shift_m25_31_values = isset($product['shift1_count']) ? $product['shift1_count'] : 0;
            } else {
                $current_shift_m25_31_values = isset($product['shift2_count']) ? $product['shift2_count'] : 0;
            }
            $current_day_m25_31_values = (isset($product['shift1_count']) ? $product['shift1_count'] : 0) + (isset($product['shift2_count']) ? $product['shift2_count'] : 0);
        }
        if ($product['product_name'] == 'SION M40/31+') {
            if ($currentTime >= '06:15:00' && $currentTime <= '14:45:00') {
                $current_shift_m40_31_values = isset($product['shift1_count']) ? $product['shift1_count'] : 0;
            } else {
                $current_shift_m40_31_values = isset($product['shift2_count']) ? $product['shift2_count'] : 0;
            }
            $current_day_m40_31_values = (isset($product['shift1_count']) ? $product['shift1_count'] : 0) + (isset($product['shift2_count']) ? $product['shift2_count'] : 0);
        }
    }
} else {
    $current_shift_m25_31_values = 0;
    $current_shift_m40_31_values = 0;
    $current_day_m25_31_values = 0;
    $current_day_m40_31_values = 0;
}
// SharedManager::print($current_shift_m25_31_values);
// SharedManager::print($current_shift_m40_31_values);

$total_current_shift_values = $current_shift_m25_31_values+$current_shift_m40_31_values;
$total_current_day_values = $current_day_m25_31_values+$current_day_m40_31_values;

$sql_monthly = "SELECT CASE 
                        WHEN p.product_name IN ('SION M25', 'SION M31') THEN 'SION M25/31'
                        WHEN p.product_name IN ('SION M40', 'SION M31+') THEN 'SION M40/31+'
                        ELSE p.product_name
                    END AS product_name,
                    s.station_name,
                    t.stage_id,
                    COUNT(CASE
                    WHEN MONTH(t.up_date) = MONTH(CURDATE()) THEN 1
                    END) AS mtd_count
                FROM   tbl_transactions t
                    JOIN tbl_product p
                        ON t.product_id = p.product_id
                    JOIN tbl_station s
                        ON t.station_id = s.station_id
                WHERE t.stage_id IN (:stage_id)
                    AND t.station_name = :station_name
                    AND MONTH(t.up_date) = MONTH(CURDATE())
                GROUP  BY p.product_name,
                        s.station_name,
                        t.stage_id
                ORDER  BY t.stage_id";

$params = [
    ":stage_id" => "7",
    ":station_name" => "A21 (Dropping)"
];

$result_monthly_data = DbManager::fetchPDOQueryData('spectra_db', $sql_monthly, $params)["data"];
// SharedManager::print($result_monthly_data);
if (!empty($result_monthly_data)) {
    foreach ($result_monthly_data as $product) {
        if ($product['product_name'] == 'SION M25/31') {
            $monthly_m25_31_values = isset($product['mtd_count']) ? $product['mtd_count'] : 0;
        }
        if ($product['product_name'] == 'SION M40/31+') {
            $monthly_m40_31_values = isset($product['mtd_count']) ? $product['mtd_count'] : 0;
        }
    }
} else {
    $monthly_m25_31_values = 0;
    $monthly_m40_31_values = 0;
}
$total_mtd_values = $monthly_m25_31_values+$monthly_m40_31_values;

$sql_yearly = "SELECT CASE 
                        WHEN p.product_name IN ('SION M25', 'SION M31') THEN 'SION M25/31'
                        WHEN p.product_name IN ('SION M40', 'SION M31+') THEN 'SION M40/31+'
                        ELSE p.product_name
                    END AS product_name,
                    s.station_name,
                    t.stage_id,
                    COUNT(CASE
                    WHEN YEAR(t.up_date) = YEAR(CURDATE()) THEN 1
                    END) AS ytd_count
                FROM   tbl_transactions t
                    JOIN tbl_product p
                        ON t.product_id = p.product_id
                    JOIN tbl_station s
                        ON t.station_id = s.station_id
                WHERE t.stage_id IN (:stage_id)
                    AND t.station_name = :station_name
                    AND YEAR(t.up_date) = YEAR(CURDATE())
                GROUP  BY p.product_name,
                        s.station_name,
                        t.stage_id
                ORDER  BY t.stage_id";

$params = [
    ":stage_id" => "7",
    ":station_name" => "A21 (Dropping)"
];

$result_yearly_data = DbManager::fetchPDOQueryData('spectra_db', $sql_yearly, $params)["data"];
// SharedManager::print($result_yearly_data);
if (!empty($result_yearly_data)) {
    foreach ($result_yearly_data as $product) {
        if ($product['product_name'] == 'SION M25/31') {
            $yearly_m25_31_values = isset($product['ytd_count']) ? $product['ytd_count'] : 0;
        }
        if ($product['product_name'] == 'SION M40/31+') {
            $yearly_m40_31_values = isset($product['ytd_count']) ? $product['ytd_count'] : 0;
        }
    }
} else {
    $yearly_m25_31_values = 0;
    $yearly_m40_31_values = 0;
}
$total_ytd_values = $yearly_m25_31_values+$yearly_m40_31_values;
?>
<link href="../css/semantic.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
<link rel="stylesheet" type="text/css" href="../css/responsive.dataTables.min.css">

<link href="../css/main.css?13" rel="stylesheet"/>
<?php include_once 'shared/headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>
<style>
    body {
        font-family: Arial, sans-serif;
        /* background-color: #f4f4f4; */
        margin: 0;
        padding: 20px;
    }

    h1 {
        text-align: center;
        color: #333;
        font-size: 2.5em;
        /* Increase font size for the title */
        font-weight: bold;
        /* Bold title */
    }

    table {
        width: 100%;
        border: 2px solid;
        border-collapse: collapse;
        margin: 20px 0;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        padding: 15px;
        /* Increase padding for more space */
        text-align: Center;
        border: 2px solid;
        border-bottom: 1px solid #ddd;
        font-size: 1.5em;
        /* Increase font size for table data */
        font-weight: bold;
        /* Bold table data */
        color: #099999;
        font-size: 37px;
    }

    th {
        background-color: #099999;
        color: white;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    @media (max-width: 600px) {

        th,
        td {
            display: block;
            text-align: right;
        }

        tr {
            margin-bottom: 15px;
            display: block;
        }

        th {
            text-align: left;
            position: relative;
            padding-left: 50%;
        }

        th::after {
            content: ':';
            position: absolute;
            left: 10px;
        }
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/dpm/day_shift_wise_viewer.php'; ?>
    <?php include_once 'shared/sidebar.php' ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                            class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">Day and Shift Wise Output</h2>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox mb-0">
                    <div id="headersegment">
                        <div id="detailsegment">
                            <div class="content-wrapper">
                                <section>
                                    <div style="text-align:Center;">
                                        <label>
                                            <h2>Date : <?php $currentDate = date('d-m-Y'); echo $currentDate; ?></h2>
                                        </label>
                                    </div>
                                    <table>
                                        <tr>
                                            <th></th>
                                            <th colspan="2" style="background-color: #00A99D; color: #fff; font-size: 25px;">Output Shift</th>
                                            <th colspan="2" style="background-color: #00A99D; color: #fff; font-size: 25px;">Output Day</th>
                                            <th colspan="2" style="background-color: #00A99D; color: #fff; font-size: 25px;">Output MTD</th>
                                            <th colspan="2" style="background-color: #00A99D; color: #fff; font-size: 25px;">Output YTD</th>
                                        </tr>
                                        <tr>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Product Name</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Plan</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Actual</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Plan</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Actual</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Plan</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Actual</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Plan</th>
                                            <th style="background-color: #00A99D; color: #fff; font-size: 25px;">Actual</th>
                                        </tr>
                                        <tr>
                                            <td style="background-color: #EEEEEE;">SION M25/31</td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">24</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $current_shift_m25_31_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">48</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $current_day_m25_31_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">1200</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $monthly_m25_31_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">7200</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $yearly_m25_31_values; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="background-color: #FFFFFF;">SION M40/31+</td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">8</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $current_shift_m40_31_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">16</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $current_day_m40_31_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">400</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $monthly_m40_31_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">2000</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $yearly_m40_31_values; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="background-color: #EEEEEE;">Total</td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">32</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $total_current_shift_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">64</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $total_current_day_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">1600</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $total_mtd_values; ?></td>
                                            <td style="background-color: #FFFFCC; color: #000000; font-size: 30px;">9200</td>
                                            <td style="background-color: #808080; color: #fff; font-size: 65px;"><?php echo $total_ytd_values; ?></td>
                                        </tr>
                                    </table>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $footer_display = 'Day and Shift Wise Output';
        include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
</div>

<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>
<script src="reports/allReports.js?<?php echo rand(); ?>"></script>
</body>
</html>