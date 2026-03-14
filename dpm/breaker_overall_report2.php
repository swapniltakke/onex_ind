<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
include('header.php');

$shift1_start = '06:15:00'; // Start time for Shift 1
$shift1_end = '14:45:00';   // End time for Shift 1
$shift2_start = '14:45:01'; // Start time for Shift 2
$shift2_end = '23:59:59';   // End time for Shift 2
$current_date = date('Y-m-d');
$first_day_of_month = date('Y-m-01');
$shift1_count = 0;
$shift2_count = 0;
$current_date_from =  date('Y-m-d');
$current_date_from = $current_date_from . " 00:00:00";
$current_date_to =  date('Y-m-d');
$current_date_to = $current_date_to . " 23:59:59";
$stage_ids = [3];

$sql_combined = "SELECT 
        p.product_name, 
        s.station_name,
        t.stage_id,
        COUNT(CASE WHEN TIME(t.up_date) BETWEEN :shift1_start AND :shift1_end THEN 1 END) AS shift1_count,
        COUNT(CASE WHEN TIME(t.up_date) BETWEEN :shift2_start AND :shift2_end THEN 1 END) AS shift2_count,
        COUNT(CASE WHEN t.up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) AS mtd_count
        FROM 
            tbl_transactions t
        JOIN 
            tbl_product p ON t.product_id = p.product_id
        JOIN 
            tbl_station s ON t.station_id = s.station_id
        WHERE 
            t.stage_id IN (:stage_id) AND
            t.up_date BETWEEN :current_date_from AND :current_date_to
        GROUP BY 
            p.product_name, s.station_name, t.stage_id
        ORDER BY t.stage_id";

$params = [
    ":shift1_start" => $shift1_start,
    ":shift1_end" => $shift1_end,
    ":shift2_start" => $shift2_start,
    ":shift2_end" => $shift2_end,
    ":stage_id" => $stage_ids,
    ":current_date_from" => $current_date_from,
    ":current_date_to" => $current_date_to
];

$res_combined = DbManager::fetchPDOQueryData('spectra_db', $sql_combined, $params)["data"];
//SharedManager::print($res_combined);
// Output structure initialization
$output = [];
$products = [];
foreach ($res_combined as $row) {
    $products[$row['product_name']] = true; // Use product names as keys
}
$products = array_keys($products); // Get unique product names as an array

//Process each row to create the desired structure
foreach ($res_combined as $row) {
    $stationName = $row['station_name'];
    $productName = $row['product_name'];

    if ($productName === 'SION M25') {
        $productName = 'SION M25/31';
    } elseif ($productName === 'SION M31') {
        // Merge with SION M25
        $productName = 'SION M25/31';
    } elseif ($productName === 'SION M31+') {
        $productName = 'SION M31+/40';
    } elseif ($productName === 'SION M40') {
        // Merge with SION M31+
        $productName = 'SION M31+/40';
    }

    $shift1 = $row['shift1_count'];
    $shift2 = $row['shift2_count'];
    $mtd = $row['mtd_count'];

    // Check if the station already exists in the output array
    if (!isset($output[$stationName])) {
        // Initialize a new station entry with default values for all products
        $output[$stationName] = ['station_name' => $stationName];
        foreach ($product_names as $product) {
            $output[$stationName]["{$product}_Shift1"] = 0;
            $output[$stationName]["{$product}_Shift2"] = 0;
            $output[$stationName]["{$product}_MTD"] = 0;
        }
    }

    // Update values for the current product
    $output[$stationName]["{$productName}_Shift1"] += $shift1;
    $output[$stationName]["{$productName}_Shift2"] += $shift2;
    $output[$stationName]["{$productName}_MTD"] += $mtd;
}

// Reindex the output array to ensure numerical keys
$output = array_values($output);
// SharedManager::print($output);

// Output array
$outputArray = [];
foreach ($output as $row) {
    foreach ($row as $key => $value) {
        if ($key === 'station_name') {
            $stationName = $value;
            continue;
        }

        // Match keys dynamically to extract product name and metric type
        if (preg_match('/^(.*?)_(Shift1|Shift2|MTD)$/', $key, $matches)) {
            $productName = $matches[1]; // Extract product name
            $metricType = $matches[2]; // Extract metric type

            // Find or initialize product entry for the current station
            $keyCombination = $stationName . '|' . $productName;
            if (!isset($outputArray[$keyCombination])) {
                $outputArray[$keyCombination] = [
                    'product_name' => $productName,
                    'station_name' => $stationName,
                    'stage_id' => 3, // Assuming stage_id is constant
                    'shift1_count' => 0,
                    'shift2_count' => 0,
                    'mtd_count' => 0,
                ];
            }

            // Assign values dynamically to the correct metric
            if ($metricType === 'Shift1') {
                $outputArray[$keyCombination]['shift1_count'] += $value;
            } elseif ($metricType === 'Shift2') {
                $outputArray[$keyCombination]['shift2_count'] += $value;
            } elseif ($metricType === 'MTD') {
                $outputArray[$keyCombination]['mtd_count'] += $value;
            }
        }
    }
}
// Reset array keys to be sequential
$outputArray = array_values($outputArray);
//SharedManager::print($outputArray);
// Group data by station name and product name
$table_data = [];
foreach ($outputArray as $row) {
    $station_name = $row['station_name'];
    $product_name = $row['product_name'];
    if (!isset($table_data[$station_name])) {
        $table_data[$station_name] = [];
    }
    $table_data[$station_name][$product_name] = [
        'shift1_count' => $row['shift1_count'],
        'shift2_count' => $row['shift2_count'],
        'mtd_count' => $row['mtd_count'],
    ];
}
// SharedManager::print($table_data);
// Extract product names for header columns
// $product_names = array_unique(array_column($outputArray, 'product_name'));
$product_names = ["SION M25/31", "SION M31+/40"];
// SharedManager::print($product_names);
$sql_station = "SELECT station_name FROM tbl_station where stage_id=:stage_id AND status=:status ORDER BY station_name";
$rs = DbManager::fetchPDOQueryData('spectra_db', $sql_station, [":stage_id" => "1,2,3", ":status" => "A"])["data"];
// SharedManager::print($rs);

foreach ($rs as $item) {
    $station_name = $item['station_name'];
    if (!array_key_exists($station_name, $table_data)) {
        $table_data[$station_name] = array(
            "SION M25" => array(
                "shift1_count" => 0,
                "shift2_count" => 0,
                "mtd_count" => 0
            ),
            "SION M40" => array(
                "shift1_count" => 0,
                "shift2_count" => 0,
                "mtd_count" => 0
            ),
            "SION M31" => array(
                "shift1_count" => 0,
                "shift2_count" => 0,
                "mtd_count" => 0
            )
        );
    }
}
// SharedManager::print($table_data);
ksort($table_data);
// SharedManager::print($table_data);
?>

<head>
    <meta http-equiv="refresh" content="10;url=breaker_overall_report3.php">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
</head>
<div class="content-wrapper">
    <section class="content-header mb-2">
        <div class="container-fluid">
            <div class="pagehead">
                <div class="row">
                    <div class=" col-md-4 col-sm-4">
                        <h5>TimeSpan Dashboard</h5>
                    </div>
                    <div class="col-sm-8">
                        <div class="tab float-sm-right">
                            <!-- <a href="Admindash.php"  class="tablinks" ><i class='fas fa-home'></i> </a>
                            <a href="#"  class="tablinks paybill" ><i class="fa fa-money"></i> </a>
                            <a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
                            <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
                            <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section>
        <div style="text-align:Center;">
            <label>
                <h2>Date : <?php $currentDate = date('d-m-Y'); echo $currentDate; ?></h2>
            </label>
        </div>
        <table>
            <thead>
                <tr>
                    <th rowspan="3">Station Name</th>
                    <th colspan="9">Product Information</th>
                </tr>
                <tr>
                    <?php foreach ($product_names as $product_name): ?>
                    <th colspan="3"><?= htmlspecialchars($product_name) ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($product_names as $product_name): ?>
                    <th>Shift 1</th>
                    <th>Shift 2</th>
                    <th>MTD</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($table_data as $station_name => $products): ?>
                <tr>
                    <td><?= htmlspecialchars($station_name) ?></td>
                    <?php foreach ($product_names as $product_name): ?>
                        <?php
                            $shift1 = isset($products[$product_name]) ? $products[$product_name]['shift1_count'] : 0;
                            $shift2 = isset($products[$product_name]) ? $products[$product_name]['shift2_count'] : 0;
                            $mtd = isset($products[$product_name]) ? $products[$product_name]['mtd_count'] : 0;
                        ?>
                        <?php if ($shift1 == 0 && $shift2 == 0 && $mtd == 0): ?>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        <?php else: ?>
                            <td><?= $shift1 ?></td>
                            <td><?= $shift2 ?></td>
                            <td><?= $mtd ?></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php include('footer.php'); ?>
<script>
    $(function() {
        var Accordion = function(el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            // Variables privadas
            var links = this.el.find('.link');
            var links1 = this.el.find('.link1');
            // Evento
            links.on('click', {
                el: this.el,
                multiple: this.multiple
            }, this.dropdown)
            links1.on('click', {
                el: this.el,
                multiple: this.multiple
            }, this.dropdown1)
        }

        Accordion.prototype.dropdown = function(e) {
            var $el = e.data.el;
            $this = $(this),
                $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');

            if (!e.data.multiple) {
                $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
            };
        }

        Accordion.prototype.dropdown1 = function(e) {
            var $el = e.data.el;
            $this = $(this),
                $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');

            if (!e.data.multiple) {
                $el.find('.submenu1').not($next).slideUp().parent().removeClass('open');
            };
        }

        var accordion = new Accordion($('#accordion'), false);
    });
</script>
<script>
    $(document).ready(function() {
        $(".icon-input-btn").each(function() {
            var btnFont = $(this).find(".btn").css("font-size");
            var btnColor = $(this).find(".btn").css("color");
            $(this).find(".fa").css({
                'font-size': btnFont,
                'color': btnColor
            });
        });
    });
</script>
</body>
</html>