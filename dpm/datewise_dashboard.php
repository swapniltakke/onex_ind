<?php
include 'DatabaseConfig.php';
session_start();
$user=$_SESSION['username'];
$pass = $_SESSION['pass'];
include('header.php');
include('menu_spectra.php');
?>
 <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            display: flex;
            justify-content: space-between; /* Space between tables */
        }

        table {
            border-collapse: collapse;
            width: 200px; /* Set a fixed width for tables */
            margin: 10px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #009999;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9; 
            height: 50px;
            font-size: 40px;
            font-weight: bold;
            
font-align: center;/* Alternate row color */
        }

        tr:hover {
            background-color: #f1f1f1; /* Row hover effect */
        }
        caption {
            display: table-caption;
            text-align: center;
        }
        .blinking {
            animation: blink 1s infinite;
        }

        
    </style>

 

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
      <div class="container-fluid">
       
		<div class="pagehead">
		 <div class="row">
          <div class=" col-md-4 col-sm-4">
            <h5 >Reports </h5>
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

    <div  style="text-align:right;"><label>Date : <?php $currentDate = date('d-m-Y'); echo $currentDate;?></label></div>
      <!-- <div class="container">  -->
    <div>
    <div><center>
    <form method="POST" action="datewise_dashboard.php">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <br>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <br>
        <input type="submit" value="Generate Report">
    </form></center>
    </div div style="top:0px; border:1px solid red;">

<?php
// Step 2: PHP Code to Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];



    // Step 4: Fetching Data from the Database
    //$sql = "SELECT * FROM tbl_transactions WHERE  station_name='SION M25/31 Pole 1' and  up_date BETWEEN ? AND ?";
   // $sql = "SELECT COUNT(*) AS total_count tbl_transactions WHERE up_date BETWEEN ? AND ?";
    $sql= "SELECT product_name, COUNT(*) AS total_count 
                FROM tbl_transactions 
                WHERE up_date BETWEEN ? AND ? 
                GROUP BY product_name";
    $stmt = odbc_prepare($conn, $sql);

    if ($stmt) {
        // Execute the prepared statement with bound parameters
        odbc_execute($stmt, array($startDate, $endDate));

        // Step 5: Displaying the Report
        
        echo "<h2>Product-wise Count from <strong>$startDate</strong> to <strong>$endDate</strong></h2>";
        echo "<table border='1' align='center'>
                <tr>
                    <th>Product Name</th>
                    <th>Actual Count</th>    
                    <th>Planed Count</th> 
                     
                   
                </tr>";

        while ($row = odbc_fetch_array($stmt)) {
            echo "<tr>
                    <td>{$row['product_name']}</td>
                     <td>6</td>
                    <td>{$row['total_count']}</td>
                   
                    
                    
                </tr>";
        }

        echo "</table>";
    } else {
        echo "Failed to prepare the SQL statement.";
    }

    // Close the ODBC connection
    odbc_close($conn);
}
?>
</div>
    

     <!-- <div class="container">    -->
   

     
    </section>
   
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
