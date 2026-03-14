<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
include('header.php');
?>
<head>
 <meta http-equiv="refresh" content="10;url=breaker_overall_report.php">
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
            font-size: 2.5em; /* Increase font size for the title */
            font-weight: bold; /* Bold title */
        }
        table {
            width: 100%;
            border: 2px solid;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px; /* Increase padding for more space */
            text-align: Center;
            border: 2px solid;
            border-bottom: 1px solid #ddd;
            font-size: 1.5em; /* Increase font size for table data */
            font-weight: bold; /* Bold table data */
            color: #099999;
            font-size:40px;
        }
        th {
            background-color: #099999;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        @media (max-width: 600px) {
            th, td {
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
 

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
      <div class="container-fluid">
       
		<div class="pagehead">
		 <div class="row">
          <div class=" col-md-4 col-sm-4">
            <h5 >TimeSpan Dashboard</h5>
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
      </div><!-- /.container-fluid -->
    </section>

    

    <!-- Main content Change by Akshay-->
    <section> 

    <div  style="text-align:Center;"><label><h2>Date : <?php $currentDate = date('d-m-Y'); echo $currentDate;?></h2></label></div>
      <!-- <div class="container">  -->
    
      <table>
    <thead>
        <tr>
            <th rowspan="2" style = "font-size:40px;">Product Name</th> 
            <th rowspan="2" style = "font-size:40px;">Station Name</th> 
            
            <!-- <th colspan="0" style = "font-size:40px;">Output</th> -->
        </tr>
        <tr>
            
            <th style = "font-size:40px;">Shift 1</th>
            <th style = "font-size:40px;">Shift 2</th>
            <th style = "font-size:40px;">MTD</th>
            <!-- <th style = "font-size:40px;">YTD</th> -->
        </tr>
    </thead>
    <tbody><!-- table for SION M25 -->
            <?php
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date = date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;
            $current_date_from =  date('Y-m-d');
            $current_date_from = $current_date_from." 00:00:00";
            $current_date_to =  date('Y-m-d');
            $current_date_to = $current_date_to. " 23:59:59";

            $sql = "SELECT 
                    product_name, 
                    COUNT(product_id) AS product_id,
                    station_name
                    FROM 
                    tbl_transactions
                    where station_name=:station_name and product_name=:product_name and stage_id =:stage_id and up_date between 
                    :current_date_from and :current_date_to
                    GROUP BY 
                    product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "SION M25/31 Pole Assembly 1", ":product_name" => "Sion M25", ":stage_id" => "3", ":current_date_from" => "$current_date_from", ":current_date_to" => "$current_date_to"])["data"];
            


            $sql_shift1 = "SELECT 
                CAST(up_date AS DATE) AS report_date,
                product_name,
                COUNT(CASE 
                        WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                        ELSE NULL
                    END) AS shift1_count,
                COUNT(CASE 
                        WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                        ELSE NULL
                    END) AS shift2_count,
                COUNT(*) AS total_count
            FROM 
                tbl_transactions
            WHERE 
                up_date BETWEEN :current_date_from AND :current_date_to
                AND stage_id =:stage_id
                AND station_name =:station_name
            GROUP BY 
                DATE(up_date),
                product_name";

            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date_from" => "$current_date_from", ":current_date_to" => "$current_date_to", ":stage_id" => "3", ":station_name" => "SION M25/31 Pole Assembly 1"])["data"];

            $sql_mtd = "SELECT 
                DATE_FORMAT(up_date, :up_date) AS month,
                COUNT(*) AS month_count
            FROM 
                tbl_transactions
            WHERE 
                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id -- Today
            GROUP BY 
                DATE_FORMAT(up_date, :up_date)
            ORDER BY 
                month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "3"])["data"];

            $sql_mtd_ytd = "SELECT 
                DATE_FORMAT(up_date, :up_date) AS month,
                COUNT(*) AS month_count,
                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
            FROM 
                tbl_transactions
            WHERE 
                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id -- Today
            GROUP BY 
                DATE_FORMAT(up_date, :up_date)
            ORDER BY 
                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "3"])["data"];
        

            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>
    <tbody><!-- table for SION M25 -->
            <?php
    
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date =  date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;
            $current_date_from =  date('Y-m-d');
            $current_date_from = $current_date_from." 00:00:00";
            $current_date_to =  date('Y-m-d');
            $current_date_to = $current_date_to. " 23:59:59";

            $sql = "SELECT 
                    product_name, 
                    COUNT(product_id) AS product_id,
                    station_name
                    FROM 
                    tbl_transactions
                    where station_name=:station_name and product_name=:product_name and stage_id =:stage_id and up_date between 
                    :current_date_from and :current_date_to
                    GROUP BY 
                    product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "SION M25/31 Pole Assembly 2", ":product_name" => "Sion M25", ":stage_id" => "3", ":current_date_from" => "$current_date_from", ":current_date_to" => "$current_date_to"])["data"];
            
            $sql_shift1 = "SELECT 
                CAST(up_date AS DATE) AS report_date,
                product_name,
                COUNT(CASE 
                        WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                        ELSE NULL
                    END) AS shift1_count,
                COUNT(CASE 
                        WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                        ELSE NULL
                    END) AS shift2_count,
                COUNT(*) AS total_count
            FROM 
                tbl_transactions
            WHERE 
                up_date BETWEEN :current_date_from AND :current_date_to
                AND stage_id =:stage_id
                AND station_name =:station_name
            GROUP BY 
                DATE(up_date),
                product_name";

            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date_from" => "$current_date_from", ":current_date_to" => "$current_date_to", ":stage_id" => "3", ":station_name" => "SION M25/31 Pole Assembly 2"])["data"];

            $sql_mtd = "SELECT 
                DATE_FORMAT(up_date, :up_date) AS month,
                COUNT(*) AS month_count
            FROM 
                tbl_transactions
            WHERE 
                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id -- Today
            GROUP BY 
                DATE_FORMAT(up_date, :up_date)
            ORDER BY 
                month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "3"])["data"];

            $sql_mtd_ytd = "SELECT 
                DATE_FORMAT(up_date, :up_date) AS month,
                COUNT(*) AS month_count,
                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
            FROM 
                tbl_transactions
            WHERE 
                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id -- Today
            GROUP BY 
                DATE_FORMAT(up_date, :up_date)
            ORDER BY 
                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "3"])["data"];
        
            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>

    <tbody><!-- table for SION B2 -->
            <?php
    
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date = date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;

            $sql = "SELECT 
                    product_name, 
                    COUNT(product_id) AS product_id,
                    station_name
                    FROM 
                    tbl_transactions
                    where station_name=:station_name and product_name=:product_name and stage_id =:stage_id
                    GROUP BY 
                    product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "B2 (M25/31 Final 1)", ":product_name" => "Sion M25", ":stage_id" => "5"])["data"];

            $sql_shift1 = "SELECT 
                        DATE(up_date) AS report_date,
                        product_name,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                            ELSE 0
                        END) AS shift1_count,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                            ELSE 0
                        END) AS shift2_count,
                        COUNT(*) AS total_count
                    FROM 
                        tbl_transactions
                    WHERE 
                        DATE(up_date) =:current_date
                        AND stage_id =:stage_id
                        AND station_name =:station_name
                    GROUP BY 
                        DATE(up_date),
                        product_name
                    ORDER BY 
                        report_date,
                        product_name";
            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "5", ":station_name" => "B2 (M25/31 Final 1)"])["data"];

            $sql_mtd = "SELECT 
                            DATE_FORMAT(up_date, :up_date) AS month,
                            COUNT(*) AS month_count
                        FROM 
                            tbl_transactions
                        WHERE 
                            up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                            AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id -- Today
                        GROUP BY 
                            DATE_FORMAT(up_date, :up_date)
                        ORDER BY 
                            month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            $sql_mtd_ytd = "SELECT 
                                DATE_FORMAT(up_date, :up_date) AS month,
                                COUNT(*) AS month_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
                            FROM 
                                tbl_transactions
                            WHERE 
                                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id
                            GROUP BY 
                                DATE_FORMAT(up_date, :up_date)
                            ORDER BY 
                                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>

    <tbody><!-- table for SION M25 B3-->
            <?php
    
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date = date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;


            $sql = "SELECT 
                    product_name, 
                    COUNT(product_id) AS product_id,
                    station_name
                    FROM 
                    tbl_transactions
                    where station_name=:station_name and product_name=:product_name and stage_id =:stage_id
                    GROUP BY 
                    product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "B3 (M25/31 Final 2)", ":product_name" => "Sion M25", ":stage_id" => "5"])["data"];

            $sql_shift1 = "SELECT 
                        DATE(up_date) AS report_date,
                        product_name,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                            ELSE 0
                        END) AS shift1_count,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                            ELSE 0
                        END) AS shift2_count,
                        COUNT(*) AS total_count
                    FROM 
                        tbl_transactions
                    WHERE 
                        DATE(up_date) =:current_date
                        AND stage_id =:stage_id
                        AND station_name =:station_name
                    GROUP BY 
                        DATE(up_date),
                        product_name
                    ORDER BY 
                        report_date,
                        product_name";
            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "5", ":station_name" => "B3 (M25/31 Final 2)"])["data"];

            $sql_mtd = "SELECT 
                            DATE_FORMAT(up_date, :up_date) AS month,
                            COUNT(*) AS month_count
                        FROM 
                            tbl_transactions
                        WHERE 
                            up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                            AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id -- Today
                        GROUP BY 
                            DATE_FORMAT(up_date, :up_date)
                        ORDER BY 
                            month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            $sql_mtd_ytd = "SELECT 
                                DATE_FORMAT(up_date, :up_date) AS month,
                                COUNT(*) AS month_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
                            FROM 
                                tbl_transactions
                            WHERE 
                                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id
                            GROUP BY 
                                DATE_FORMAT(up_date, :up_date)
                            ORDER BY 
                                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>


    <tbody><!-- table for SION M25 B4 -->
            <?php
    
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date = date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;

            $sql = "SELECT 
            product_name, 
            COUNT(product_id) AS product_id,
            station_name
            FROM 
            tbl_transactions
            where station_name=:station_name and product_name=:product_name and stage_id =:stage_id
            GROUP BY 
            product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "B4 (M25/31 Final 3)", ":product_name" => "Sion M25", ":stage_id" => "5"])["data"];

            $sql_shift1 = "SELECT 
                        DATE(up_date) AS report_date,
                        product_name,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                            ELSE 0
                        END) AS shift1_count,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                            ELSE 0
                        END) AS shift2_count,
                        COUNT(*) AS total_count
                    FROM 
                        tbl_transactions
                    WHERE 
                        DATE(up_date) =:current_date
                        AND stage_id =:stage_id
                        AND station_name =:station_name
                    GROUP BY 
                        DATE(up_date),
                        product_name
                    ORDER BY 
                        report_date,
                        product_name";
            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "5", ":station_name" => "B4 (M25/31 Final 3)"])["data"];

            $sql_mtd = "SELECT 
                            DATE_FORMAT(up_date, :up_date) AS month,
                            COUNT(*) AS month_count
                        FROM 
                            tbl_transactions
                        WHERE 
                            up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                            AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id -- Today
                        GROUP BY 
                            DATE_FORMAT(up_date, :up_date)
                        ORDER BY 
                            month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            $sql_mtd_ytd = "SELECT 
                                DATE_FORMAT(up_date, :up_date) AS month,
                                COUNT(*) AS month_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
                            FROM 
                                tbl_transactions
                            WHERE 
                                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id
                            GROUP BY 
                                DATE_FORMAT(up_date, :up_date)
                            ORDER BY 
                                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>


    <tbody> <!-- table for SION M25 B5 -->
            <?php
    
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date = date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;

            $sql = "SELECT 
            product_name, 
            COUNT(product_id) AS product_id,
            station_name
            FROM 
            tbl_transactions
            where station_name=:station_name and product_name=:product_name and stage_id =:stage_id
            GROUP BY 
            product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "B5 (M25/31 Final 4)", ":product_name" => "Sion M25", ":stage_id" => "5"])["data"];

            $sql_shift1 = "SELECT 
                        DATE(up_date) AS report_date,
                        product_name,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                            ELSE 0
                        END) AS shift1_count,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                            ELSE 0
                        END) AS shift2_count,
                        COUNT(*) AS total_count
                    FROM 
                        tbl_transactions
                    WHERE 
                        DATE(up_date) =:current_date
                        AND stage_id =:stage_id
                        AND station_name =:station_name
                    GROUP BY 
                        DATE(up_date),
                        product_name
                    ORDER BY 
                        report_date,
                        product_name";
            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "5", ":station_name" => "B5 (M25/31 Final 4)"])["data"];

            $sql_mtd = "SELECT 
                            DATE_FORMAT(up_date, :up_date) AS month,
                            COUNT(*) AS month_count
                        FROM 
                            tbl_transactions
                        WHERE 
                            up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                            AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id -- Today
                        GROUP BY 
                            DATE_FORMAT(up_date, :up_date)
                        ORDER BY 
                            month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            $sql_mtd_ytd = "SELECT 
                                DATE_FORMAT(up_date, :up_date) AS month,
                                COUNT(*) AS month_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
                            FROM 
                                tbl_transactions
                            WHERE 
                                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id
                            GROUP BY 
                                DATE_FORMAT(up_date, :up_date)
                            ORDER BY 
                                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>

    <tbody> <!-- table for SION M25 A2 -->
            <?php
    
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date = date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;

            $sql = "SELECT 
                    product_name, 
                    COUNT(product_id) AS product_id,
                    station_name
                    FROM 
                    tbl_transactions
                    where station_name=:station_name and product_name=:product_name and stage_id =:stage_id
                    GROUP BY 
                    product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "A18-A19 ( Functional Test 1)", ":product_name" => "Sion M25", ":stage_id" => "5"])["data"];

            $sql_shift1 = "SELECT 
                        DATE(up_date) AS report_date,
                        product_name,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                            ELSE 0
                        END) AS shift1_count,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                            ELSE 0
                        END) AS shift2_count,
                        COUNT(*) AS total_count
                    FROM 
                        tbl_transactions
                    WHERE 
                        DATE(up_date) =:current_date
                        AND stage_id =:stage_id
                        AND station_name =:station_name
                    GROUP BY 
                        DATE(up_date),
                        product_name
                    ORDER BY 
                        report_date,
                        product_name";
            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "5", ":station_name" => "A18-A19 ( Functional Test 1)"])["data"];

            $sql_mtd = "SELECT 
                            DATE_FORMAT(up_date, :up_date) AS month,
                            COUNT(*) AS month_count
                        FROM 
                            tbl_transactions
                        WHERE 
                            up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') -- First day of the current month
                            AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id -- Today
                        GROUP BY 
                            DATE_FORMAT(up_date, :up_date)
                        ORDER BY 
                            month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            $sql_mtd_ytd = "SELECT 
                                DATE_FORMAT(up_date, :up_date) AS month,
                                COUNT(*) AS month_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
                            FROM 
                                tbl_transactions
                            WHERE 
                                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id
                            GROUP BY 
                                DATE_FORMAT(up_date, :up_date)
                            ORDER BY 
                                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "5"])["data"];

            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>

    <tbody> <!-- table for SION M25 Dropping5 -->
            <?php
    
            
            $shift1_start = '06:15:00'; // Start time for Shift 1
            $shift1_end = '14:45:00';   // End time for Shift 1
            $shift2_start = '14:45:01'; // Start time for Shift 2
            $shift2_end = '23:59:59';   // End time for Shift 2
            $current_date = date('Y-m-d');
            $first_day_of_month = date('Y-m-01');
            $shift1_count = 0;
            $shift2_count = 0;

            $sql = "SELECT 
                    product_name, 
                    COUNT(product_id) AS product_id,
                    station_name
                    FROM 
                    tbl_transactions
                    where station_name=:station_name and product_name=:product_name and stage_id =:stage_id
                    GROUP BY 
                    product_name, station_name";
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_name" => "A21 (Dropping)", ":product_name" => "Sion M25", ":stage_id" => "7"])["data"];

            $sql_shift1 = "SELECT 
                        DATE(up_date) AS report_date,
                        product_name,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift1_start AND :shift1_end THEN 1
                            ELSE 0
                        END) AS shift1_count,
                        SUM(CASE 
                            WHEN TIME(up_date) BETWEEN :shift2_start AND :shift2_end THEN 1
                            ELSE 0
                        END) AS shift2_count,
                        COUNT(*) AS total_count
                    FROM 
                        tbl_transactions
                    WHERE 
                        DATE(up_date) =:current_date
                        AND stage_id =:stage_id
                        AND station_name =:station_name
                    GROUP BY 
                        DATE(up_date),
                        product_name
                    ORDER BY 
                        report_date,
                        product_name";
            $shift1_res = DbManager::fetchPDOQueryData('spectra_db', $sql_shift1, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "7", ":station_name" => "A21 (Dropping)"])["data"];

            $sql_mtd = "SELECT 
                            DATE_FORMAT(up_date, :up_date) AS month,
                            COUNT(*) AS month_count
                        FROM 
                            tbl_transactions
                        WHERE 
                            up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                            AND up_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id=:stage_id 
                        GROUP BY 
                            DATE_FORMAT(up_date, :up_date)
                        ORDER BY 
                            month";
            $mtd_res = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd, [":up_date" => "2024-10", ":stage_id" => "7"])["data"];

            $sql_mtd_ytd = "SELECT 
                                DATE_FORMAT(up_date, :up_date) AS month,
                                COUNT(*) AS month_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN 1 END) AS year_to_date_count,
                                COUNT(CASE WHEN up_date >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND up_date < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR THEN 1 END) AS year_to_end_count
                            FROM 
                                tbl_transactions
                            WHERE 
                                up_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                                AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) and stage_id =:stage_id
                            GROUP BY 
                                DATE_FORMAT(up_date, :up_date)
                            ORDER BY 
                                month";
            $mtd_res_ytd = DbManager::fetchPDOQueryData('spectra_db', $sql_mtd_ytd, [":up_date" => "2024-10", ":stage_id" => "7"])["data"];

            foreach ($res as $data) {
                echo "  <tr>\n";   
                echo "  <td >".  $data["product_name"] . "\n";
                echo "  <td>" . $data["station_name"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift1_count"] . "\n";
                echo "  <td>" . $shift1_res[0]["shift2_count"] . "\n";
                echo "  <td>" . $mtd_res[0]["month_count"] . "\n";
                // echo "  <td>" . $mtd_res_ytd[0]["month_count"] . "\n";
                echo "</tr>\n";
            }
            ?>
    </tbody>
    
</table>
     <!-- <div class="container">    -->

      <!-- <div class="container">  -->
        
    
     
    </section>
    </div>
   <!--  <section class="content">
      <div class="container-fluid">
     

        <div class="row">
          <div class="col-md-12 text-center">
             Default box 
				
				<div class="mdwidth">
				<div class="table_container">
					<table width="100%">
					<thead>
						<tr>
							<th>Sr. No.</th>
							<th>Tasks Pending to do</th>
							<th>----</th>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td>1.</td>
						<td>Task Name comes here </td>
						<td>---</td>
					</tr>
					<tr>
						<td>2.</td>
						<td>Task Name comes here </td>
						<td>---</td>
					</tr>
					<tr>
						<td>3.</td>
						<td>Task Name comes here </td>
						<td>---</td>
					</tr>
					<tr>
						<td>4.</td>
						<td>Task Name comes here </td>
						<td>---</td>
					</tr>
					</tbody>
					</table>
					</div>
				</div>
				
				

          </div>
        </div>
      </div>
    </section> -->
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
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
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
		links1.on('click', {el: this.el, multiple: this.multiple}, this.dropdown1)
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
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});
</script>
</body>
</html>