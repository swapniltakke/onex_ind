<?php
include 'DatabaseConfig.php';
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
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
            width: 100%; /* Set a fixed width for tables */
            margin: 10px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            max-width: 120px; 
            overflow: hidden;
            white-space: nowrap; 
            text-overflow: ellipsis;
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
            <h5 >Daily Status Dashboard</h5>
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
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='SION M25/31 Pole 1' and product_name='Sion M25' and stage_id = '3'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

      <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>
            <th>
            <th>
            <th>
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='B2 (M25/31 Final 1)' and product_name='SION M25' and stage_id = '5'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

       <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='B3 (M25/31 Final 2)' and product_name='SION M25' and stage_id = '5'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

     
       <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='No Load - A' and product_name='SION M25' and stage_id = '7'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

      <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='No Load - B' and product_name='SION M25' and stage_id = '6'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

     <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='A14 (mV Drop 1)' and product_name='SION M25' and stage_id = '7'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

     <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='A19-A20' and product_name='SION M25' and stage_id = '7'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

     <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='A19-A20 (Functional Test 2)' and product_name='SION M25' and stage_id = '7'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

      <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='B19 (HV Test)' and product_name='SION M25' and stage_id = '7'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

     <!-- <div class="container">  -->
    <div>
        <table style="width:100%">
            <!-- <caption>SION M25 Product Details</caption> -->
            <tr>
            <th>Product Name
            <th>Station Name
            <th>Planed Quantity
            <th>Actual Quantity
                
            </tr>

            <?php

                //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
                $sql = "SELECT 
                product_name, 
                COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
                station_name
            FROM 
                tbl_transactions
            where station_name='A21 (Dropping)' and product_name='SION M25' and stage_id = '7'
            GROUP BY 
                product_name, station_name";
                $res = odbc_exec($conn, $sql);

                if ($res == FALSE) die ("could not execute statement $sql<br />");

                while (odbc_fetch_row($res)) // while there are rows
                {
                echo "  <tr>\n";   
                echo "  <td >".  odbc_result($res, "product_name") . "\n";
                echo "  <td>" . odbc_result($res, "station_name") . "\n";
                echo "  <td>" . 6 . "\n";
                echo "  <td>" . odbc_result($res, "product_id") . "\n";
                
                echo "</tr>\n";
                }
            ?>
        </table>
     </div>

     <!-- <div class="container">    -->

     
    
   


     
    

     
     
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
