<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];

// include('shared/CommonManager.php');
include_once 'core/index.php';
include_once 'header.php';
SharedManager::checkAuthToModule(15);


// Define shift timings
$shift1_start = '06:15:00'; // Start time for Shift 1
$shift1_end = '14:45:00';   // End time for Shift 1
$shift2_start = '14:45:01'; // Start time for Shift 2
$shift2_end = '23:15:00';   // End time for Shift 2
$current_date = date('Y-m-d');

// Query to fetch data (modify according to your table structure)
 $sql = "
    SELECT 
        CAST(up_date AS DATE) AS report_date,
        product_name,
        SUM(CASE 
            WHEN CAST(up_date AS TIME) BETWEEN :shift1_start AND :shift1_end THEN 1 
            ELSE 0 
        END) AS shift1_count,
        SUM(CASE 
            WHEN CAST(up_date AS TIME) BETWEEN :shift2_start AND :shift2_end THEN 1 
            ELSE 0 
        END) AS shift2_count,
        COUNT(*) AS total_count
    FROM 
        tbl_transactions
    WHERE 
        CAST(up_date AS DATE) =:current_date AND 
        stage_id =:stage_id AND station_name =:station_name
    GROUP BY 
        CAST(up_date AS DATE),
        product_name
    ORDER BY 
        report_date, product_name
";
 $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "3", ":station_name" => "SION M25/31 Pole Assembly 1"])["data"];

// Initialize an array to hold the data
$data = [];
// SharedManager::print($result);

// Fetch the results
foreach ($result as $row) {
    $data[] = $row;
}

 foreach ($data as $entry) {
     "Date: " . $entry['report_date'] . 
    ", Product: " . $entry['product_name'] .
    ", Shift 1 Count: " . $entry['shift1_count'] . 
     ", Shift 2 Count: " . $entry['shift2_count'] . 
     ", Total Count: " . $entry['total_count'] . "<br>";
}
 //Query from latest 5 records 

 $sql_droping = "
 SELECT 
     CAST(up_date AS DATE) AS report_date,
     product_name,
     COUNT(*) AS total_count
 FROM 
     tbl_transactions
 WHERE 
     CAST(up_date AS DATE) =:current_date AND stage_id=:stage_id AND station_name =:station_name
 GROUP BY 
     CAST(up_date AS DATE),
     product_name
 ORDER BY 
     report_date, product_name
";
$result_droping = DbManager::fetchPDOQueryData('spectra_db', $sql_droping, [":current_date" => "$current_date", ":stage_id" => "7", ":station_name" => "A21 (Dropping)"])["data"];

// Initialize an array to hold the data
$data_droping = [];
// SharedManager::print($result_droping);

foreach ($result_droping as $row) {
    $data_droping[$row['product_name']] = $row['total_count'];
}
// SharedManager::print($data_droping);

$sql1 = "SELECT barcode FROM tbl_transactions ORDER BY tr_id DESC LIMIT 15";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql1)["data"];
  
 //Query from Modal records 
$current_date_from =  date('Y-m-d');
$current_date_from = $current_date_from." 00:00:00";
$current_date_to =  date('Y-m-d');
$current_date_to = $current_date_to. " 23:59:59";

$sql11 = "SELECT 
            t.tr_id,
            t.user_id,
            t.station_name,
            t.barcode,
            t.up_date,
            us.username,
            us.ip_address,
            us.login_time,
            us.session_expiry,
            us.is_active
        FROM tbl_transactions t
        JOIN tbl_user_sessions us ON t.user_id = us.username
        WHERE 
            us.is_active = '1'
            AND t.up_date BETWEEN :current_date_from AND :current_date_to
            AND us.login_time BETWEEN :current_date_from AND :current_date_to
        GROUP BY 
            t.user_id
        ORDER BY 
            t.up_date DESC";

$res_main = DbManager::fetchPDOQueryData('spectra_db', $sql11, [":current_date_from" => "$current_date_from", ":current_date_to" => "$current_date_to"])["data"];
 
//Query from Modal records 
$sql2 = "SELECT w.title,p.product_name,s.station_name,st.stage_name,t.create_date FROM tbl_transactiondetails AS t
inner join tbl_workbench w on w.id=t.workbench_id
inner join tbl_product p on p.product_id=t.product_id
inner join tbl_station s on s.station_id=t.station_id
inner join tbl_stage st on st.stage_id = t.tr_id
WHERE t.create_date BETWEEN :current_date_from AND :current_date_to
AND t.status=:status
ORDER BY t.trdet_id DESC";
$addon = DbManager::fetchPDOQueryData('spectra_db', $sql2, [":current_date_from" => "$current_date_from", ":current_date_to" => "$current_date_to", ":status" => "1"])["data"];

// SharedManager::print($addon);
// SharedManager::print($res_main);
// exit;
//Query from Barchart records 
$sql3 = "SELECT tr_id,cust_name,station_name,up_date,
        MONTH(up_date) AS month_number,
        YEAR(up_date) AS year
    FROM tbl_transactions WHERE station_name='SION M25/31 Pole 1'
    ORDER BY up_date";
$res3 = DbManager::fetchPDOQueryData('spectra_db', $sql3)["data"];

// $quarterly_sales = [0, 0, 0, 0]; 


$quarter_map = [
    1 => 0, // January -> Q1
    2 => 0, // February -> Q1
    3 => 0, // March -> Q1
    4 => 1, // April -> Q2
    5 => 1, // May -> Q2
    6 => 1, // June -> Q2
    7 => 2, // July -> Q3
    8 => 2, // August -> Q3
    9 => 2, // September -> Q3
    10 => 3, // October -> Q4
    11 => 3, // November -> Q4
    12 => 3  // December -> Q4
];

// foreach ($res3 as $row) {
//     $month = $row['month_number'];
//     $quarter = $quarter_map[$month];
//     $quarterly_sales[$quarter] += 1;
// }

// Define labels for quarters
$quarters = ['Feb', 'Mar' , 'Apr' , 'May' , 'Jun' ];
$quarterly_sales = ['Feb' => '341','Mar' => '526','Apr' => '521','May' => '557','Jun' => '197'];
// SharedManager::print($quarterly_sales);
// SharedManager::print($quarters);

?>

<head>
    <meta http-equiv="refresh" content="10;url=new_layout.php">
    <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=0.6, user-scalable=no">
    <style>
        body {
    transform-origin: top left; /* Align the origin of transformation */
    transform: scale(0.6); /* Reduce size to 50% */
    width: 167%; /* Counteract the scaling by increasing width */
    height: 167%; /* Counteract the scaling by increasing height */
}
    /* Container for the three columns */
    .row-one {
        display: flex;
        /* Flexbox layout */
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 20px;
        /* Adds space between rows */
        height: 100px;
        /* Fixed height for row one */
    }

    /* Container for the four tiles */
    .row-two {
        display: flex;
        /* Flexbox layout */
        justify-content: space-between;
        /* Distributes the tiles evenly */
        gap: 10px;
        height: 400px;
        /* Increased height for row two */
    }

    /* Styling for each tile/column */
    .tile {
        background-color: #ffff;
        color: #099999;
        text-align: center !important;
        padding: 50px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        font-size: 30px;
        flex: 1;
        /* Ensures all tiles take equal space */
        display: flex;
        justify-content: left;
        align-items: left;
    }

    .tile_up {
        background-color: #ffff;
        color: #099999;
        text-align: center;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        font-size: 30px;
        flex: 1;
        /* Ensures all tiles take equal space */
        display: flex;
        justify-content: left;
        align-items: left;
    }

    .nested-tiles {
        display: flex;
        /* Flexbox for horizontal layout */
        justify-content: space-between;
        /* Space between the small tiles */
        width: 100%;
        /* Ensure the nested tiles take up full width */
        gap: 10px;
        /* Adds space between the two small tiles */
    }

    .nested-tile {
        background-color: #ffff;
        /* Different color for small tiles */
        color: #099999;
        text-align: center;
        padding: 8px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(26, 25, 25, 0.1);
        font-size: 30px;
        flex: 1;
        /* Ensures both nested tiles take equal space */
    }

    /* Optional: Adjust the padding for the chart in Tile 1 */
    .tile canvas {
        width: 100% !important;
        height: 300px !important;
        /* Increased height for the bar chart */
    }

    /* For row one tiles, adjust the padding to make them smaller */
    .row-one .tile {
        padding: 20px;
        /* Smaller padding for row one */
    }

    .count-number {
        font-size: 150px;
        /* Large number size */
        font-weight: bold;
        color: #009999;
    }

    /* Outer container border */
    .outer-container {
        border: 1px solid #000;
        /* Outer border */
        padding: 10px;
        width: 100%;
        /* Make the outer container full-width */
    }

    /* Container that holds all rows (renamed class) */
    .blocks-container {
        display: flex;
        flex-direction: column;
        /* Arrange blocks in a vertical column (rows) */
        gap: 20px;
        /* Space between each row */
        border: 2px solid #333;
        /* Border around the entire container */
        padding: 20px;
    }

    /* Each row of blocks */
    .row {
        display: flex;
        /* Display blocks in a row */
        gap: 5px;
        /* Space between blocks */
        flex-wrap: wrap;
        /* Allow blocks to wrap if the row exceeds width */
        border: 2px solid #333;
        /* Border around each row */
        padding: 10px;
    }

    .row1 {
        display: flex;
        /* Display blocks in a row */
        gap: 0px;
        /* Space between blocks */
        flex-wrap: wrap;
        /* Allow blocks to wrap if the row exceeds width */
        border: 2px solid #333;
        /* Border around each row */
        padding: 10px;
    }

    /* Style for each block */
    .block {
        flex: 1 0 5%;
        /* Each block takes 5% of the row, adjusted for gaps */
        max-width: calc(100% / 19 - 5px);
        /* Limit each block width */
        height: 100px;
        /* Set a fixed height for blocks */
        background-color: #FFFF00;
        /* Background color for blocks */
        border: 2px solid #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 30px;
    }

    .block1 {
        flex: 1 0 5%;
        /* Each block takes 5% of the row, adjusted for gaps */
        max-width: calc(100% / 19 - 5px);
        /* Limit each block width */
        height: 100px;
        /* Set a fixed height for blocks */
        background-color: #ffff;
        /* Background color for blocks */
        /*   border: 2px solid #333; Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 20px;
    }

    .block2 {
        flex: 1 0 5%;
        /* Each block takes 5% of the row, adjusted for gaps */
        max-width: calc(100% / 19 - 5px);
        /* Limit each block width */
        height: 100px;
        /* Set a fixed height for blocks */
        background-color: #FF0000;
        /* Background color for blocks */
        /*   border: 2px solid #333; Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
    }

    .block3 {
        flex: 1 0 5%;
        /* Each block takes 5% of the row, adjusted for gaps */
        max-width: calc(100% / 19 - 5px);
        /* Limit each block width */
        height: 100px;
        /* Set a fixed height for blocks */
        background-color: #FFFF00;
        /* Background color for blocks */
        border: 5px dashed #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 30px;
    }

    .block4 {
        flex: 1 0 5%;
        /* Each block takes 5% of the row, adjusted for gaps */
        max-width: calc(100% / 19 - 5px);
        /* Limit each block width */
        height: 70px;
        /* Set a fixed height for blocks */
        background-color: #239ED0;
        /* Background color for blocks */
        /*   border: 2px solid #333; Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 18px;

    }

    .block5 {
        flex: 1 0 5%;
        /* Each block takes 5% of the row, adjusted for gaps */
        max-width: calc(100% / 19 - 5px);
        /* Limit each block width */
        height: 80px;
        /* Set a fixed height for blocks */
        background-color: #EBA487;
        /* Background color for blocks */
        /*   border: 2px solid #333; Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 18px;
        border-style: double;

    }

    .block6 {
        flex: 1 0 5%;
        /* Each block takes 5% of the row, adjusted for gaps */
        max-width: calc(100% / 19 - 5px);
        /* Limit each block width */
        height: 100px;
        /* Set a fixed height for blocks */
        background-color: #FFFF00;
        /* Background color for blocks */
        border: 5px inset #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 30px;
    }

    .block7 {

        width: 40px;
        height: 40px;
        /* Set a fixed height for blocks */
        background-color: #FFFF00;
        /* Background color for blocks */
        border: 5px inset #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 10px;
    }

    .block8 {

        width: 40px;
        height: 40px;
        /* Set a fixed height for blocks */
        background-color: #FFFF00;
        /* Background color for blocks */
        border: 5px dashed #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 10px;
    }

    .block9 {

        width: 40px;
        height: 40px;
        /* Set a fixed height for blocks */
        background-color: rgb(5, 100, 56);
        /* Background color for blocks */
        border: 2px solid #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 10px;
    }

    .block10 {

        width: 40px;
        height: 40px;
        /* Set a fixed height for blocks */
        background-color: #FFFF00;
        /* Background color for blocks */
        border: 2px solid #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 35px;
    }

    .block11 {

        width: 40px;
        height: 40px;
        /* Set a fixed height for blocks */
        background-color: hsl(17, 100.00%, 50.00%);
        /* Background color for blocks */
        border: 2px solid #333;
        /* Border for blocks */
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 35px;
    }

    /* Different color for the first and last row */
    .first-row,
    .last-row {
        background-color: #ffff;
        /* Change color for first and last rows */
    }

    /* Middle rows with a different color */
    .middle-row {
        background-color: #ffff;
        /* Color for the middle rows */
    }

    .hlftble {
        display: flex;
        justify-content: flex-end;
        /* Aligns content to the right */
        /* padding: 10px; */
    }

    .tblscroll {
        max-height: 300px;
        /* Adjust height to fit your needs */
        overflow-y: scroll;
        display: block;
        border: 1px solid #ccc;
        /* Optional, for better visual appearance */
    }

    .tblscroll table {
        width: 100%;
        border-collapse: collapse;
    }

    .tblscroll thead {
        position: sticky;
        top: 0;
        background-color: #fff;
        /* Optional: helps to distinguish header */
        z-index: 1;
        /* Ensures header stays on top */
    }

    .tblscroll th,
    .tblscroll td {
        padding: 8px;
        text-align: left;
        /* border: 1px solid #ddd; */
    }

    .hover-div {
        flex: 1 0 5%;
        max-width: calc(100% / 19 - 5px);
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-weight: bold;
        font-size: 30px;
        cursor: pointer;

    }

    .card1 {
        background-color: rgb(255, 255, 255);
        border-radius: 10px;
        width: 500px;
        height: 650px;
        padding: 5px;
        box-shadow: 0 4px 15px rgb(0, 0, 0);
        display: none;
        /* Initially hidden */
        position: absolute;
        top: 100px;
        z-index: 10;
    }

    .hover-div:hover+.card1 {
        display: block;
        font-size: 30px;
        color: rgb(0, 0, 0);

    }

    .title {
        font-size: 30px;
        margin-bottom: 10px;
        text-align: center;
    }

    .time {
        font-size: 20px;
        color: #000;
        margin-bottom: 5px;
        text-align: center;
    }

    .step {
        position: relative;
        padding-left: 40px;
        margin-left: 10px;
        border-left: 2px solid #009999;
        margin-bottom: 15px;
        cursor: pointer;
    }

    .step:last-child {
        border-left: none;
    }

    .step:hover .tooltip {
        display: block;
    }

    .tooltip {
        display: none;
        position: absolute;
        left: 45px;
        top: 5px;
        background-color: #009999;
        color: #fff;
        padding: 5px;
        border-radius: 5px;
        font-size: 12px;
        z-index: 10;
    }

    .operator {
        margin-top: 10px;
        font-size: 14px;
    }

    .divider {
        border-top: 2px solid #009999;
        margin: 10px 0;
    }

    .success-icon {
        text-align: center;
        font-size: 40px;
        color: #009999;
    }

    .icon {
        position: absolute;
        left: 5px;
        top: 5px;
        font-size: 30px;
        color: #009999;
    }

    .step-content {
        margin-left: 50px;
        text-align: left;
    }

    /* Blinking background effect */
    /* Blinking text effect */
    @keyframes blinkText {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    .blink-text {
        font-size: 30px;
        font-weight: bold;
        text-align: center;
        color: red;
        animation: blinkText 1s infinite;
    }

    /* Blinking block background effect */
    @keyframes blink {
        0% {
            background-color: #FFFF00;
            /* Default background color */
        }

        50% {
            background-color: rgb(0, 107, 0);
            /* Blinking color */
        }

        100% {
            background-color: #FFFF00;
            /* Return to default */
        }
    }

    .blinking-block {
        animation: blink 1s infinite;
        /* Blinks every 1 second */
    }

    @keyframes blink1 {
        0% {
            background-color: #FFFF00;
            /* Default background color */
        }

        50% {
            background-color: #FF0000;
            /* Blinking color */
        }

        100% {
            background-color: #FFFF00;
            /* Return to default */
        }
    }

    .blinking-block-red {
        animation: blink1 1s infinite;
        /* Blinks every 1 second */
    }

    .tile {
        /* Existing styles */
        position: relative;
    }

    .tile-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        background-color: #099999;
        color: #fff;
        padding: 10px;
        text-align: center;
        border-radius: 8px 8px 0 0;
    }

    .tile-header h3 {
        margin: 0;
    }

    .nested-tiles {
        margin-top: 20px;
        /* Adjust this value to create more space between the header and the content */
    }
    </style>
</head>

<body style="background: linear-gradient(to bottom, rgba(0, 0, 40, 255), rgba(0, 150, 151, 255));">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header mb-2">
            <div class="container-fluid">

                <div class="pagehead">
                    <!-- <div class="row">
           <div class=" col-md-4 col-sm-4">
            <h5 >Line Status</h5>
          </div> 
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
 
            </div>
           
          </div> 
		  </div> -->
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div class="card">
                            <div class="card-body formarea smpadding">

                                <!--new code for layout  -->
                                <!-- Row with three columns -->
                                <div class="row-one">
                                    <div class="tile">
                                        <h1
                                            style="color:#009999; font-size:50px; font-famiy:Siemens sans Black;  font-weight: bold; ">
                                            SIEMENS</h1>
                                    </div>
                                    <div class="tile">
                                        <h1
                                            style="color:#009999; font-size:50px; font-famiy:Siemens sans Black;  font-weight: bold; ">
                                            VCB Production Status Board</h1>
                                    </div>
                                    <div class="tile">
                                        <h1
                                            style="color:#009999; font-size:50px; font-famiy:Siemens sans Black;  font-weight: bold; ">
                                            Date :&nbsp;&nbsp;<?php echo date('Y-m-d') ; ?></h1>

                                    </div>
                                </div>

                                <!-- <div class="row-one">
                                <div class="tile_up">
                                    <span style="color:#009999; font-size:30px; font-famiy:Siemens sans Black;  font-weight: bold; text-align:center;">Graph: Monthly Output</span>
                                    </div>
                                    <div class="tile_up">
                                    <h1 style="color:#009999; font-size:30px; font-famiy:Siemens sans Black;  font-weight: bold; ">Today Output Product Wise</h1>
                                    </div>
                                    <div class="tile_up">
                                    <h1 style="color:#009999; font-size:30px; font-famiy:Siemens sans Black;  font-weight: bold; ">Daily Output Order Information</h1>
                                    </div>
                                    <div class="tile_up">
                                    <h1 style="color:#009999; font-size:30px; font-famiy:Siemens sans Black;  font-weight: bold; ">Real Time Alarm Information</h1>
                                    </div>
                                </div> -->

                                <!-- Row with four tiles below -->

                                <div class="row-two">
                                    <div class="tile">
                                        <div class="tile-header">
                                            <h3>SION M Breaker Output</h3>
                                        </div>
                                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                        <canvas id="chartWithTooltips" width="400" height="280" style="margin-top: 26px;"></canvas>
                                    </div>
                                    <div class="tile">
                                        <div class="tile-header">
                                            <h3>Daily Output</h3>
                                        </div>
                                        <div class="nested-tiles">
                                            <div class="nested-tile"><b>SION M25/31</b>
                                                <p class="count-number">
                                                    <?php 
                                                        $result_m25 = (isset($data_droping['SION M25']) ? $data_droping['SION M25'] : 0) + (isset($data_droping['SION M31']) ? $data_droping['SION M31'] : 0);
                                                        echo $result_m25;
                                                    ?>
                                                </p>
                                            </div>
                                            <div class="nested-tile"><b>SION M31+/40</b>
                                                <p class="count-number">
                                                    <?php 
                                                        $result_m40 = (isset($data_droping['SION M31+']) ? $data_droping['SION M31+'] : 0) + (isset($data_droping['SION M40']) ? $data_droping['SION M40'] : 0);
                                                        echo $result_m40;
                                                    ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tile">
                                        <div class="tile-header">
                                            <h3>Daily Output Details</h3>
                                        </div>
                                        <div class="tblscroll" style="margin-top: 40px;">
                                            <table style="border:1px; width:750px">
                                                <thead>
                                                    <tr>
                                                        <th>Serial No.</th>
                                                        <th>Sales No.</th>
                                                        <th>Item No.</th>
                                                        <th>Production Order No.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                    foreach ($res as $row) {
                        $barcode = trim($row['barcode']);
                        $product_data['serial_no'] = substr($barcode, 0, 8);
                        $product_data['sales_no'] = substr($barcode, -29, 10);
                        $product_data['item_no'] = substr($barcode, -18, 6);
                        $product_data['prod_no'] = substr($barcode, -12);

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($product_data['serial_no']) . "</td>";
                        echo "<td>" . htmlspecialchars($product_data['sales_no']) . "</td>";
                        echo "<td>" . htmlspecialchars($product_data['item_no']) . "</td>";
                        echo "<td>" . htmlspecialchars($product_data['prod_no']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tile">
                                        <div class="tile-header">
                                            <h3>ANDON</h3>
                                        </div>
                                        <div class="blink-text">
                                            <?php
            foreach ($addon as $data) {
            ?>
                                            <span>&#8226;<a href="index.php" target="_blank"
                                                    style="color: red; text-decoration: none; !important"><?php echo htmlspecialchars($data["title"])." - "; ?>
                                                    <?php echo htmlspecialchars($data["product_name"])." - "; ?>
                                                    <?php echo htmlspecialchars($data["station_name"]); ?></a></span><br>
                                            <?php
            }
            ?>
                                        </div>
                                    </div>
                                </div>

                                <br>
                                <!-- Default boxxxxx -->

                                <div class="container-fluid">
                                    <div class="outer-container">

                                        <div class="blocks-container">
                                            <!-- <div class="row1 first-row">
            
            <div class="block1"></div>
            <div class="block4"></div>
            <div class="block4"> </div>
            <div class="block4">Functional Testing</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4">Mechanical Endurance Test</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4">Final Assembly</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4">Shaft & Pole Assembly</div>
            <div class="block4"></div>
            <div class="block4"></div>
        </div>   -->
                                            <table border="1">
                                                <tr>

                                                    <th
                                                        style="width: 750px; font-size: 25px; background-color: #239ED0;  border: 2px solid #000000;">
                                                        Functional Testing</th>
                                                    <th
                                                        style="width: 470px;  font-size: 25px; background-color: #239ED0;  border: 2px solid #000000">
                                                        Mechanical Endurance Test</th>
                                                    <th
                                                        style="width: 480px;  font-size: 25px; background-color: #239ED0;  border: 2px solid #000000;">
                                                        Final Assembly</th>
                                                    <th
                                                        style="font-size: 25px; background-color: #239ED0;  border: 2px solid #000000;">
                                                        Shaft & Pole Assembly</th>
                                                </tr>
                                            </table>
                                            <!-- <div class="row1 first-row">
            
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block5">3AK</div>
            <div class="block5"></div>
            <div class="block5"></div>
            <div class="block5">SION M40/31+</div>
            <div class="block5">SION 31+</div>
            <div class="block5"></div>
            <div class="block5">SION 40</div>
            <div class="block5"></div>
            <div class="block5"></div>
        </div>-->
                                            <!-- <div class="hlftble">
        <table style="width: 47%;">
            <tr>
                <th style="width: 230px; font-size: 25px; background-color: #EBA487;  border: 2px solid #000000;">3AK</th>
                <th style="width: 250px;  font-size: 25px; background-color: #EBA487;  border: 2px solid #000000">SION M40/31+</th>
                <th style="width: 120px;  font-size: 25px; background-color: #EBA487;  border: 2px solid #000000;">SION M31+</th>
                <th style="font-size: 25px; background-color: #EBA487;  border: 2px solid #000000;">SION M40</th>
            </tr>
        </table>
        </div> -->
                                            <div class="row first-row">
                                                <!-- 19 blocks in the first row -->
                                                <div class="block1">Dropping</div>
                                                <div class="block1">Cosima 2</div>
                                                <div class="block1">Cosima 1</div>
                                                <div class="block1">mV Drop+Stock Setting</div>
                                                <div class="block1">mV Drop+Stock Setting</div>
                                                <div class="block1">mV Drop+Stock Setting</div>
                                                <div class="block1">No Load 4</div>
                                                <div class="block1">No Load 3</div>
                                                <div class="block1">No Load 2</div>
                                                <div class="block1">No Load 1</div>
                                                <div class="block1">3AK Assembly</div>
                                                <div class="block1">3AK Assembly</div>
                                                <div class="block1">SION40 Assembly</div>
                                                <div class="block1">SION M31+/40 Assembly</div>
                                                <div class="block1">SION M31+ Pole Assembly 2</div>
                                                <div class="block1">SION M40 Pole Assembly 2</div>
                                                <div class="block1">Shaft Assembly 2</div>
                                                <div class="block1">SION M40 Pole Assembly 1</div>
                                                <div class="block1">Shaft Assembly 1</div>

                                            </div>

                                            <!-- Second Row with 19 blocks -->
                                            <div class="row middle-row">
                                                <!-- 19 blocks in the second row -->
                                                <div class="block <?php 
                $search_dropping_value = 'A21 (Dropping)';
                $matching_add_on_dropping_station = array_search(strtolower($search_dropping_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_dropping_station = array_search(strtolower($search_dropping_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_dropping = array_search(strtolower($search_dropping_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_dropping_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_dropping_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">A21</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_dropping !== false) {
                                $barcode = trim($res_main[$key_dropping]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_dropping]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_dropping]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block">A20</div>
                                                <div class="block">A19</div>
                                                <div class="block">A16</div>
                                                <div class="block">A15</div>
                                                <div class="block <?php 
                $search_a14_value = 'A14 (mV Drop 1)';
                $matching_add_on_a14_station = array_search(strtolower($search_a14_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_a14_station = array_search(strtolower($search_a14_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_a14 = array_search(strtolower($search_a14_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_a14_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_a14_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">A14</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_a14 !== false) {
                                $barcode = trim($res_main[$key_a14]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_a14]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_a14]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block">A13</div>
                                                <div class="block">A12</div>
                                                <div class="block">A11</div>
                                                <div class="block <?php 
                $search_no_load_a_value = 'No Load - A';
                $matching_add_on_no_load_a_station = array_search(strtolower($search_no_load_a_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_no_load_a_station = array_search(strtolower($search_no_load_a_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_no_load_a = array_search(strtolower($search_no_load_a_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_no_load_a_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_no_load_a_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">A10</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_no_load_a !== false) {
                                $barcode = trim($res_main[$key_no_load_a]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_no_load_a]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_no_load_a]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_a7_value = 'A7';
                $matching_add_on_a7_station = array_search(strtolower($search_a7_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_a7_station = array_search(strtolower($search_a7_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_a7 = array_search(strtolower($search_a7_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_a7_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_a7_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">A7</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_a7 !== false) {
                                $barcode = trim($res_main[$key_a7]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_a7]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_a7]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_a6_value = 'A6';
                $matching_add_on_a6_station = array_search(strtolower($search_a6_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_a6_station = array_search(strtolower($search_a6_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_a6 = array_search(strtolower($search_a6_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_a6_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_a6_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">A6</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_a6 !== false) {
                                $barcode = trim($res_main[$key_a6]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_a6]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_a6]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_a5_value = 'A5( Final Testing 2)';
                $matching_add_on_a5_station = array_search(strtolower($search_a5_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_a5_station = array_search(strtolower($search_a5_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_a5 = array_search(strtolower($search_a5_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_a5_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_a5_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">A5</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_a5 !== false) {
                                $barcode = trim($res_main[$key_a5]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_a5]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_a5]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_a2_value = 'A2(M31+/40 Final 1)';
                $matching_add_on_a2_station = array_search(strtolower($search_a2_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_a2_station = array_search(strtolower($search_a2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_a2 = array_search(strtolower($search_a2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_a2_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_a2_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">A2</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_a2 !== false) {
                                $barcode = trim($res_main[$key_a2]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_a2]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_a2]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block3 <?php 
                $search_m31_plus_pa_value = 'SION M31+ Pole Assembly';
                $matching_add_on_m31_plus_pa_station = array_search(strtolower($search_m31_plus_pa_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_m31_plus_pa_station = array_search(strtolower($search_m31_plus_pa_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_m31_plus_pa = array_search(strtolower($search_m31_plus_pa_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_m31_plus_pa_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_m31_plus_pa_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">X</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_m31_plus_pa !== false) {
                                $barcode = trim($res_main[$key_m31_plus_pa]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_m31_plus_pa]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_m31_plus_pa]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block3 <?php 
                $search_m40_pa_2_value = 'SION M40 Pole Assembly 2';
                $matching_add_on_m40_pa_2_station = array_search(strtolower($search_m40_pa_2_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_m40_pa_2_station = array_search(strtolower($search_m40_pa_2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_m40_pa_2 = array_search(strtolower($search_m40_pa_2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_m40_pa_2_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_m40_pa_2_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">X</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_m40_pa_2 !== false) {
                                $barcode = trim($res_main[$key_m40_pa_2]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_m40_pa_2]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_m40_pa_2]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block3">X</div>
                                                <div class="block3 <?php 
                $search_m40_pa_1_value = 'SION M40 Pole Assembly 1';
                $matching_add_on_m40_pa_1_station = array_search(strtolower($search_m40_pa_1_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_m40_pa_1_station = array_search(strtolower($search_m40_pa_1_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_m40_pa_1 = array_search(strtolower($search_m40_pa_1_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_m40_pa_1_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_m40_pa_1_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">X</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_m40_pa_1 !== false) {
                                $barcode = trim($res_main[$key_m40_pa_1]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_m40_pa_1]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_m40_pa_1]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block3">X</div>
                                            </div>

                                            <!-- Third Row with 19 blocks -->
                                            <div class="row middle-row">
                                                <!-- 19 blocks in the third row -->
                                                <div class="block">B19</div>
                                                <div class="block">B18</div>
                                                <div class="block">B17</div>
                                                <div class="block2"></div>
                                                <div class="block2"></div>
                                                <div class="block2"></div>
                                                <div class="block">B10</div>
                                                <div class="block">B9</div>
                                                <div class="block">B8</div>
                                                <div class="block <?php 
                $search_no_load_b_value = 'No Load - B';
                $matching_add_on_no_load_b_station = array_search(strtolower($search_no_load_b_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_no_load_b_station = array_search(strtolower($search_no_load_b_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_no_load_b = array_search(strtolower($search_no_load_b_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_no_load_b_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_no_load_b_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">B7</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_no_load_b !== false) {
                                $barcode = trim($res_main[$key_no_load_b]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_no_load_b]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_no_load_b]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_b5_final_4_value = 'B5 (M25/31 Final 4)';
                $matching_add_on_b5_final_4_station = array_search(strtolower($search_b5_final_4_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_b5_final_4_station = array_search(strtolower($search_b5_final_4_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_b5_final_4 = array_search(strtolower($search_b5_final_4_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_b5_final_4_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_b5_final_4_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">B5</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_b5_final_4 !== false) {
                                $barcode = trim($res_main[$key_b5_final_4]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_b5_final_4]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_b5_final_4]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_b4_final_3_value = 'B4 (M25/31 Final 3)';
                $matching_add_on_b4_final_3_station = array_search(strtolower($search_b4_final_3_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_b4_final_3_station = array_search(strtolower($search_b4_final_3_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_b4_final_3 = array_search(strtolower($search_b4_final_3_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_b4_final_3_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_b4_final_3_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">B4</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_b4_final_3 !== false) {
                                $barcode = trim($res_main[$key_b4_final_3]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_b4_final_3]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_b4_final_3]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_b3_final_2_value = 'B3 (M25/31 Final 2))';
                $matching_add_on_b3_final_2_station = array_search(strtolower($search_b3_final_2_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_b3_final_2_station = array_search(strtolower($search_b3_final_2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_b3_final_2 = array_search(strtolower($search_b3_final_2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_b3_final_2_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_b3_final_2_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">B3</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_b3_final_2 !== false) {
                                $barcode = trim($res_main[$key_b3_final_2]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_b3_final_2]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_b3_final_2]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block6 <?php 
                $search_b2_final_1_value = 'B2 (M25/31 Final 1))';
                $matching_add_on_b2_final_1_station = array_search(strtolower($search_b2_final_1_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_b2_final_1_station = array_search(strtolower($search_b2_final_1_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_b2_final_1 = array_search(strtolower($search_b2_final_1_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_b2_final_1_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_b2_final_1_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">B2</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_b2_final_1 !== false) {
                                $barcode = trim($res_main[$key_b2_final_1]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_b2_final_1]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_b2_final_1]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block3 <?php 
                $search_m25_31_pa_2_value = 'SION M25/31 Pole Assembly 2';
                $matching_add_on_m25_31_pa_2_station = array_search(strtolower($search_m25_31_pa_2_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_m25_31_pa_2_station = array_search(strtolower($search_m25_31_pa_2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_m25_31_pa_2 = array_search(strtolower($search_m25_31_pa_2_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_m25_31_pa_2_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_m25_31_pa_2_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">X</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_m25_31_pa_2 !== false) {
                                $barcode = trim($res_main[$key_m25_31_pa_2]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_m25_31_pa_2]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_m25_31_pa_2]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block3">X</div>
                                                <div class="block3 <?php 
                $search_m25_31_pa_1_value = 'SION M25/31 Pole Assembly 1';
                $matching_add_on_m25_31_pa_1_station = array_search(strtolower($search_m25_31_pa_1_value), array_map('strtolower', array_column($addon, 'station_name')));
                $matching_m25_31_pa_1_station = array_search(strtolower($search_m25_31_pa_1_value), array_map('strtolower', array_column($res_main, 'station_name')));
                $key_m25_31_pa_1 = array_search(strtolower($search_m25_31_pa_1_value), array_map('strtolower', array_column($res_main, 'station_name')));
                if ($matching_add_on_m25_31_pa_1_station !== false) {
                    echo 'blinking-block-red'; 
                } else {
                    if ($matching_m25_31_pa_1_station !== false) {
                        echo 'blinking-block'; 
                    } else {
                        echo '';
                    }
                }
            ?>">
                                                    <div class="hover-div">X</div>
                                                    <div class="card1">
                                                        <div class="title">Station Information</div>
                                                        <div class="time"><?php echo date('l jS \of F Y h:i:s A');?>
                                                        </div>
                                                        <?php
                            if ($key_m25_31_pa_1 !== false) {
                                $barcode = trim($res_main[$key_m25_31_pa_1]['barcode']);
                                $row['serial_no'] = substr($barcode, 0, 8);
                                $row['sales_no'] = substr($barcode, -29, 10);
                                $row['item_no'] = substr($barcode, -18, 6);
                                $row['prod_no'] = substr($barcode, -12);
                                
                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
                                echo "<div class='step-content'><strong>" . htmlspecialchars($res_main[$key_m25_31_pa_1]['station_name']) . "</strong></div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
                                echo "<div class='step-content'>";
                                echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
                                echo "<p><strong>Wo No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
                                echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
                                echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
                                echo "</div>";
                                echo "</div>";

                                echo "<div class='divider'></div>";
                                echo "<div class='step'>";
                                echo "<div class='icon'><i class='fas fa-user'></i></div>";
                                echo "<div class='step-content'><strong>User: " . htmlspecialchars($res_main[$key_m25_31_pa_1]['user_id']) . "</strong></div>";
                                echo "</div>";
                                echo "<div class='divider'></div>";
                            }
                        ?>
                                                        <div class="success-icon"><i class="fas fa-check"></i></div>
                                                    </div>
                                                </div>
                                                <div class="block3">X</div>
                                                <!-- <div class="block3"></div> -->
                                            </div>

                                            <!-- Fourth Row with 19 blocks -->
                                            <div class="row last-row">
                                                <!-- 19 blocks in the fourth row -->
                                                <div class="block1">HV Test</div>
                                                <div class="block1">Cosima 4</div>
                                                <div class="block1">Cosima 3</div>
                                                <div class="block1">Rework</div>
                                                <div class="block1">Rework</div>
                                                <div class="block1">Rework</div>
                                                <div class="block1">No Load 4</div>
                                                <div class="block1">No Load 3</div>
                                                <div class="block1">No Load 2</div>
                                                <div class="block1">No Load 1</div>
                                                <div class="block1">B5 Finally Assembly 1</div>
                                                <div class="block1">B4 Finally Assembly 1</div>
                                                <div class="block1">B3 Finally Assembly 1</div>
                                                <div class="block1">B2 Finally Assembly 1</div>
                                                <div class="block1">SION M25/31.5 Pole Assembly 2</div>
                                                <div class="block1">Shaft Assembly 2</div>
                                                <div class="block1">SION M25/31.5 Pole Assembly 1</div>
                                                <div class="block1">Shaft Assembly 1</div>
                                                <!-- <div class="block">76</div> -->
                                            </div>
                                            <!-- <div class="row1 first-row">
            
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block1"></div>
            <div class="block5"></div>
            <div class="block5"></div>
            <div class="block5"></div>
            <div class="block5"></div>
            <div class="block5">SION M25/31.5</div>
            <div class="block5"></div>
            <div class="block5"></div>
            <div class="block5"></div>
            <div class="block5"></div>
        </div>  -->
                                            <!-- <div class="hlftble">
        <table style="width: 47%;">
            <tr>
                <th style="width: 250px;  font-size: 25px; background-color: #EBA487;  border: 2px solid #000000">SION M25/31.5</th>
            </tr>
        </table>
        </div> -->
                                            <!-- <div class="row1 first-row">
            
            <div class="block4"></div>
            <div class="block4">Functional Testing</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4">Rework</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4">Mechanical Endurance Test</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4">Final Assembly</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4">Shaft & Pole Assembly</div>
            <div class="block4"></div>
            <div class="block4"></div>
            <div class="block4"></div>
        </div>  -->
                                            <table>
                                                <tr>

                                                    <th
                                                        style="width: 350px; font-size: 25px; background-color: #239ED0;  border: 2px solid #000000;">
                                                        Functional Testing</th>
                                                    <th
                                                        style="width: 380px; font-size: 25px; background-color: #239ED0;  border: 2px solid #000000;">
                                                        Rework</th>
                                                    <th
                                                        style="width: 480px;  font-size: 25px; background-color: #239ED0;  border: 2px solid #000000">
                                                        Mechanical Endurance Test</th>
                                                    <th
                                                        style="width: 480px;  font-size: 25px; background-color: #239ED0;  border: 2px solid #000000;">
                                                        Final Assembly</th>
                                                    <th
                                                        style="font-size: 25px; background-color: #239ED0;  border: 2px solid #000000;">
                                                        Shaft & Pole Assembly</th>
                                                </tr>
                                            </table>
                                        </div><br>

                                        <div class="hlftble">
                                            <table>
                                                <tr>
                                                    <th text-align: center; padding: 10px;">Notations</th>
                                                </tr>
                                                <tr>
                                                    <!-- <td style="border: 1px solid #000; text-align: center; padding: 10px;">
                
                <div>Notation</div>
            </td> -->
                                                    <td
                                                        style="border: 1px solid #000; text-align: center; padding: 10px;">
                                                        <div class="block10">X</div>
                                                        <div>Idle</div>
                                                    </td>
                                                    <td
                                                        style="border: 1px solid #000; text-align: center; padding: 10px;">
                                                        <div class="block9"></div>
                                                        <div>Working</div>
                                                    </td>
                                                    <td
                                                        style="border: 1px solid #000; text-align: center; padding: 10px;">
                                                        <div class="block11">R</div>
                                                        <div>Alarm</div>
                                                    </td>
                                                    <td
                                                        style="border: 1px solid #000; text-align: center; padding: 10px;">
                                                        <div class="block11"></div>
                                                        <div>Rework</div>
                                                    </td>
                                                    <td
                                                        style="border: 1px solid #000; text-align: center; padding: 10px;">
                                                        <div class="block7"></div>
                                                        <div>Final Assembly</div>
                                                    </td>
                                                    <td
                                                        style="border: 1px solid #000; text-align: center; padding: 10px;">
                                                        <div class="block8"></div>
                                                        <div>Sub Assembly</div>
                                                    </td>




                                                </tr>
                                                <tr>

                                                </tr>
                                            </table>
                                        </div>


                                    </div>
                                </div>


                                <!-- Default boxxxxx -->


                            </div>

                        </div>
                    </div>
                </div>
            </div>
    </div>



    <div class="container-fluid">













    </div>
    </div>




    </div>
    </div>
    </div>
    </section>
    <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php include('footer.php');?>
    <!-- ./wrapper -->

    <!-- jQuery -->
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

    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <script src="js/fontawesome4.js"></script>
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
    // Get modal and button elements
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("openModalBtn");
    var span = document.getElementsByClassName("close")[0];
    //var dataContainer = document.getElementById("dataContainer");

    // Fetch data from server when button is clicked
    btn.onclick = function() {
        dataContainer.innerHTML = 'Loading...'; // Show loading message

        fetch('fetchcode.php')
            .then(response => response.json()) // Parse JSON response
            .then(data => {
                dataContainer.innerHTML = ''; // Clear previous data

                if (data.length > 0) {
                    const item = data[0]; // Get the first item
                    dataContainer.innerHTML = `
                    Product Name: ${item.product_name} <br>
                    User ID: ${item.user_id} <br>
                    Station Name: ${item.station_name} <br>
                    Barcode: ${item.barcode} <br>
                    Update Date: ${item.up_date} <br>
                `;
                } else {
                    dataContainer.innerHTML = 'No data found.'; // Message for no data
                }

                modal.style.display = "block"; // Show modal
            })
            .catch(() => {
                dataContainer.innerHTML = 'Failed to load data. Please try again later.';
            });
    }


    // Close modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
    <script>
    var ctx = document.getElementById('chartWithTooltips').getContext('2d');

    var chartWithTooltips = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($quarters); ?>,
            datasets: [{
                label: 'SION M Monthly Breaker Output',
                data: <?php echo json_encode($quarterly_sales); ?>,
                backgroundColor: '#099999',
                borderColor: '#ffff',
                borderWidth: 2
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleColor: 'white',
                    bodyColor: 'white'
                },
                legend: {
                    position: 'top',
                    labels: {
                        fontColor: 'blue'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
    <script>
    var modal = document.getElementById("customModal");
    var btn = document.getElementById("customModalBtn");
    var close = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }
    close.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) modal.style.display = "none";
    }
    </script>
</body>

</html>