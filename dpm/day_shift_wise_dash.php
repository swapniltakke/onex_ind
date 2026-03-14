<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
include('header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta http-equiv="refresh" content="10;url=breaker_overall_report2.php">
  <title>DPM | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400">   
    <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css"> 
	
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 40 4px 40px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 20px;
            width: 600px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .product-name {
            font-size: 4em;
            color: #099999;
            margin-bottom: 10px;
        }
        .total-count {
            font-size: 7em;
            color: #099999;
            margin: 10px 0;
        }
        .shift-counts {
            display: flex;
            justify-content: space-between;
        }
        .shift-count {
            flex: 2;
            text-align: center;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
            margin: 0 5px;
            font-size: 3em;
        }
        .arrow {
        font-size: 1.5em; /* Increase size for better visibility */
        vertical-align: middle; /* Aligns the arrow nicely with text */
    }
    .up-arrow {
        color: green; /* Green for up arrow */
    }
    .down-arrow {
        color: red; /* Red for down arrow */
    }
  
</style>
</head>
<body class="hold-transition sidebar-mini sidebar-collapse" style="background:url('img/bg.jpg') left no-repeat;	background-size:cover;">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar for pay bill login -->
  <!-- <nav class="main-header navbar navbar-expand navbar-white  navbar-light"> -->
  
  <!-- Navbar for gpf login  use the bottom line of code for GPF login-->
 <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-gpf navbar-light"> -->
 
  <!-- Navbar for gpf login  use the bottom line of code for income tax login-->
 <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-income navbar-light"> -->
 
 <!-- Navbar for gpf login  use the bottom line of code for Employee login-->
 <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-light"> -->
  
    <!-- Left navbar links -->
    <!-- <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
     
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link">USER ID </span>
      </li>
    </ul> -->

    
    <!-- Right navbar links -->
    <!-- <ul class="navbar-nav ml-auto"> -->
      <!-- Messages Dropdown Menu -->
     
      <!-- Notifications Dropdown Menu -->
<!--       
       <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link"></span>
      </li> -->
     <!-- <li class="nav-item">
        <img src="img/emb.gif" alt="" height="55" />
      </li>-->
    <!-- </ul>
  </nav> -->
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <!-- <aside class="main-sidebar sidebar-dark-primary elevation-4"> -->
    <!-- Brand Logo -->
  

    <!-- Sidebar -->
   
     
      <!-- Sidebar Menu -->
      
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
      <div class="container-fluid">
       
		<div class="pagehead">
		 <div class="row">
          <div class=" col-md-4 col-sm-4">
            <h5 >Shift Metrics Dashboard</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <!-- <a href="afterLogin.html"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
  <a href="#"  class="tablinks paybill" ><i class="fa fa-money"></i> </a>
  	<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
    <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
	  <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a> -->
</div>
           
          </div>
		  </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12 text-center">
            <!-- Default box -->
				
				 <div class="mdwidth">
<?php

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
$result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":shift1_start" => "$shift1_start", ":shift1_end" => "$shift1_end", ":shift2_start" => "$shift2_start", ":shift2_end" => "$shift2_end", ":current_date" => "$current_date", ":stage_id" => "7", ":station_name" => "A21 (Dropping)"])["data"];

// Initialize an array to hold the data
$data = [];

// Fetch the results
foreach ($result as $row) {
    $data[] = $row;
}

// Display the results
// foreach ($data as $entry) {
//     echo "Date: " . $entry['report_date'] . 
//     ", Product: " . $entry['product_name'] .
//     ", Shift 1 Count: " . $entry['shift1_count'] . 
//     ", Shift 2 Count: " . $entry['shift2_count'] . 
//     ", Total Count: " . $entry['total_count'] . "<br>";
// }


?>
 
<h2>Date: <?php echo $current_date; ?></h2>
<div class="container">
    <?php foreach ($data as $entry): ?>
        <div class="card">
            <div class="product-name"><?php echo htmlspecialchars($entry['product_name']); ?></div>
            <div class="total-count"><?php echo htmlspecialchars($entry['total_count']); ?></div>
            <div class="shift-counts">
                <div class="shift-count">
                   I-Shift: <?php echo htmlspecialchars($entry['shift1_count']); ?>
                   <div>
                        <?php 
                        $total_minutes = 480; // Total minutes
                        $count_per_shift = 80; // Minutes per count
                        $max_count = $total_minutes / $count_per_shift; // Calculate maximum counts (6 in this case)

                        $shift1_count = (int)$entry['shift1_count'];
                        
                        // Check if the current count exceeds the maximum allowable count
                        if ($shift1_count >= $max_count): ?>
                            <span class="arrow up-arrow">&#9650;</span> <!-- Green up arrow -->
                        <?php else: ?>
                            <span class="arrow down-arrow">&#9660;</span> <!-- Red down arrow -->
                        <?php endif; ?>
                    </div>
                        </div>  
                <div class="shift-count">
                    II-Shift: <?php echo htmlspecialchars($entry['shift2_count']); ?>
                    <div>
                        <?php 
                        $total_minutes = 480; // Total minutes
                        $count_per_shift = 80; // Minutes per count
                        $max_count = $total_minutes / $count_per_shift; // Calculate maximum counts (6 in this case)

                        $shift2_count = (int)$entry['shift2_count'];
                        
                        // Check if the current count exceeds the maximum allowable count
                        if ($shift2_count >= $max_count): ?>
                            <span class="arrow up-arrow">&#9650;</span> <!-- Green up arrow -->
                        <?php else: ?>
                            <span class="arrow down-arrow">&#9660;</span> <!-- Red down arrow -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; 
    
    // Close the connection
?>
</div>
				

          </div>
        </div>
      </div>
    </section>


    
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php include('footer.php'); ?>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
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

</body>
</html>
