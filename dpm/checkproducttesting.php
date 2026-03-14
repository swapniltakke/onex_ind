<?php
session_start();
$user=$_SESSION['username'];
$user_id = $_SESSION['user_id'];
$pass = $_SESSION['pass'];
include 'DatabaseConfig.php';
$conn = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);
if (!$conn) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());	
}


$sql = "SELECT * FROM tbl_stage where stage_type='Testing'";
	
$query = mysqli_query($conn, $sql);

$sqlworkbench="select * from tbl_workbench";
$querywork = mysqli_query($conn,$sqlworkbench);

$sql1 = "SELECT * FROM tbl_transactions where user_id='".$user_id."'";
	
$query1 = mysqli_query($conn, $sql1);

if (!$query) {
	die ('SQL Error: ' . mysqli_error($conn));
}
if (!isset($_SESSION['username']) || $_SESSION['username'] == '')
{

			  
        echo '<script type="text/JavaScript">';
					//echo 'alert("Number of parameters not matched");';
					echo 'top.window.document.location="logout.php"';
					echo '</script>';
					exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DPM | Dashboard</title>
<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400">   
    <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css"> 
	
	<!-- Css for datepicker -->
	  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
	    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">

  <!-- Css for datatable -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    
	<style>

  .btn-circle.btn-xl {
    width: 60px;
    height: 60px;
    padding: 10px 16px;
    border-radius: 35px;
    font-size: 20px;
    line-height: 1.33;
    background-color: #bbb;
    border-color: black;
}

.btn-circle {
    width: 25px;
    height: 25px;
    padding: 6px 0px;
    border-radius: 15px;
    text-align: center;
    font-size: 12px;
    line-height: 1.42857;
}
.dot {
  height: 25px;
  width: 25px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
}
.dot_inprogress {
  height: 25px;
  width: 25px;
  background-color: #2d9ab8;
  border-radius: 50%;
  display: inline-block;
  
}
.dot_complete {
  height: 25px;
  width: 25px;
  background-color: #28a745;
  border-radius: 50%;
  display: inline-block;
  
}
.dot_error{
  height: 25px;
  width: 25px;
  background-color: #FF0000;
  border-radius: 50%;
  display: inline-block;
  
}
</style>
</head>
<body class="hold-transition sidebar-mini sidebar-collapse" style="background:url('img/bg.jpg') left no-repeat;	background-size:cover;">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar for pay bill login -->
  <nav class="main-header navbar navbar-expand navbar-white  navbar-light">
  
  <!-- Navbar for gpf login  use the bottom line of code for GPF login-->
 <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-gpf navbar-light"> -->
 
  <!-- Navbar for gpf login  use the bottom line of code for income tax login-->
 <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-income navbar-light"> -->
 
 <!-- Navbar for gpf login  use the bottom line of code for Employee login-->
 <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-light"> -->
  
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
     
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link"><?php echo strtoupper($user); ?></span>
      </li>
    </ul>

    
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
     
      <!-- Notifications Dropdown Menu -->
      
       <li class="nav-item d-none d-sm-inline-block">
       <span class="nav-link"><i class="nav-icon fas fa-user"></i> <?php echo $pass; ?></span>
      </li>
     <!-- <li class="nav-item">
        <img src="img/emb.gif" alt="" height="55" />
      </li>-->
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.html" class="brand-link">
      <img src="img/logo.jpg" alt="MahaEIS Logo" class="brand-image img-circle elevation-3" >
      <span class="brand-text font-weight-light"><br/>
	  <small style="font-size:0.6em;"></small></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
	
	
    <ul id="accordion" class="accordion nav nav-pills nav-sidebar flex-column">
		<li><div class="link"><a href="Admindash.php"><i class="fa fa-dashboard"></i><span>Dashboard</span></a></div></li>
  <li><div class="link"><a href="barcodegenerate.php"><i class="fa fa-dashboard"></i><span>Start</span></a></div></li>

<li><div class="link"><a href="checkproductsteps.php"><i class="fa fa-dashboard"></i><span>Staging Test</span></a></div></li>
<li><div class="link"><a href="checkproducttesting.php"><i class="fa fa-dashboard"></i><span>Testing Test</span></a></div></li>
<li><div class="link"><a href="checksubassembly.php"><i class="fa fa-dashboard"></i><span>Subassembly Test</span></a></div></li>

  
  
  
    
   <li>
   <div class="link"><A href="logout.php"> <i class="fa fa-sign-out nav-icon"></i><span>Logout</span></a></div>
   
  </li>
  
  
  
</ul>
	
	
	
	
	
	
     
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
            <h5 >Welcome to Digital Production Management</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <a href="#"  class="tablinks" ><i class="fas fa-home"></i> </a>
  <!--<a href="#"  class="tablinks paybill" ><i class="fa fa-money"></i> </a>
  	<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
    <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
	  <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a>-->
</div>
           
          </div>
		  </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
        
              
			  <div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-address-card"></i> Product Details</h3>
					</div>
				</div>
			   </div>
			  <div class="card">
			   <div class="card-body formarea smpadding">
         <div class="row">
			    
          <div class="col-md-2">
           <label>Serial No: &nbsp;&nbsp;<span id="serial_nm"></span></label>
         </div>
         
         <div class="col-md-3">
           <label>Sale Order No: &nbsp;&nbsp;<span id="sale_ord_no"></span></label>
         </div>
         <div class="col-md-2">
           <label>Panel No: &nbsp;&nbsp;<span id="panel_nm"></span></label>
         </div>
         <div class="col-md-3">
           <label>Customer Name: &nbsp;&nbsp;<span id="customer_name"></span></label>
         </div>
         <div class="col-md-2">
           <label>Quantity: &nbsp;&nbsp;<span id="quantity"></span></label>
         </div>
          </div>
			   
			
			   </div>
              <!-- /.card-body -->
              <!--<div class="card-footer">
                Footer
              </div>-->
              <!-- /.card-footer-->
            </div>
			
			
			
			
            <!-- /.card -->
            <div class="row">
			<div class="col-md-8">
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i>PRECHECK </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   
			   <div class="row">
				<div class="col-md-12">
					<div class="display_table">
            <input type="hidden" id="checkstage_id" value="1"> 
           
					<table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Checklist Item</th>
                    <th>Actual</th>
                    <th>Remarks</th>
                    
                  </tr>
                  </thead>
                 
                  <tfoot>
                  <tr>
                  <th>Sr. No.</th>
                    <th>Checklist Item</th>
                    <th>Actual</th>
                    <th>Remarks</th>
                   
                  </tr>
                  </tfoot>
                </table>
               
                <br>
             <p align="right"><input type="button" class="btn btn-primary" id="check_submit" name="Submit" value="Submit"> </p>

					</div>
				</div>
				
			   </div>
			   </div>
              
            </div>
</div>
<div class="col-md-4">
<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i>Workbench Navigator </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
         <div class="row">
         <?php $i=1; while($row=mysqli_fetch_array($querywork)) { ?>
          <div class="col-md-4"><?php echo $row['title_icon']."<br>".$row['title']; ?>
         </div>
         <?php $i++;  } ?>
         </div>
            <br>
			  
         
      
         
			   <div class="row">
			
			
       
     </div>
     </div>
     </div>
     <!-- Source Label Section -->
     <div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3>Safety Compliance [PPE] </h3>
					</div>
				</div>
			   </div>

         <div class="card">
			   <div class="card-body formarea smpadding">
       
 
 <div class="row">&nbsp;&nbsp;&nbsp; <span class="dot"></span>&nbsp; Initial
 &nbsp;&nbsp;<span class="dot_inprogress"></span> &nbsp; In Progress
 &nbsp;&nbsp;<span class="dot_complete"></span> &nbsp; Complete
 &nbsp;&nbsp;<span class="dot_error"></span> &nbsp; Error
  </div>



         </div>
         </div>
         <div class="card">
			   <div class="card-body formarea smpadding">
         <div class="row"><img src="./img/sanclock.gif">
         
    
    <p id="hours"></p>
         </div>

         </div></div>

<!-- End Label Section -->
</div>
  
			
<div class="card col-md-8">
			   <div class="card-body formarea smpadding">
<div class="row">
<div>&nbsp;&nbsp;&nbsp;</div>
<?php $i=1; $j=0;
$arr=array('<i class="fa fa-check-circle-o" style="font-size:24px;"></i>','<i class="fab fa-slack" style="font-size:24px;"></i>','<i class="fa fa-folder-open" style="font-size:24px;"></i>','<i class="fa fa-bullseye" style="font-size:24px"></i>','<i class="fa fa-check-square-o" style="font-size:24px;" ></i>','<i class="fa fa-check-square" style="font-size:24px"></i>','<i class="fa fa-calendar-check-o" style="font-size:24px"></i>','<i class="fas fa-clipboard-list" style="font-size:24px;"></i>');
while($row=mysqli_fetch_array($query)) { 
if($i == 1){
echo '<div><h7>'.strtoupper($row[1]).'</h7><br><button type="button" id="div_check'.$i.'"  title="" class="btn btn-default btn-circle btn-xl">'.$arr[$j].'
                            </button>
</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
} else 
{
echo '<div><h7>'.strtoupper($row[1]).'</h7><br><button type="button" id="div_check'.$i.'"  title="" class="btn btn-white btn-circle btn-xl">'.$arr[$j].'
                            </button>
</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
}
$j++; $i++; } ?>

</div>

</div></div>
 <!-- Row div End -->                           
</div>
			
			
			
			</div>

          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    
  <div class="row">
	  <div class="col-md-6"><b>Disclaimer :</b> For any queries, please contact email abc@example.com .
	  </div>
	  
<div class="col-md-6 text-right">This software is to be used by Siemens only.</div>
<div class="col-md-12 text-center">
 Copyright © 2021. All Rights Reserved.
Site Designed and Developed By <a href="https://www.spectratechindia.com/" target="_blank" title="Spectra Tech (Link Opens in a New Window)" >Spectra Tech, Pune</a>.
   </div>
    
  </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->


<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Date picker jquery -->
<script src="plugins/select2/js/select2.full.min.js"></script>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/inputmask/jquery.inputmask.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>



<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<script src="js/fontawesome4.js"></script>
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});
</script>
<script>
  $(function () {

    $('#example1').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
"scrollY": 300,
'language': {
        "lengthMenu": "_MENU_ per page",
        "zeroRecords": "No records found",
        "info": "Showing <b>_START_ to _END_</b> (of _TOTAL_)",
        "infoFiltered": "",
        "infoEmpty": "No records found",
        "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>'
    },
	  'destroy': true,
	    "sAjaxSource": "load_prechecktest.php",
         "aoColumns": [
                    {mData: 'sr_no'},
					{mData: 'check_item'},
					{mData: 'actual_opt'},
                     {mData: 'remark'}
                ]
			
    });
  });
  $('#check_submit').on('click', function(){
     
      var remarkcnt = new Array();

      //remarkcnt.splice(0, remarkcnt.length);
   
$("input[name='remark[]']").each(function(){
  if( $(this).val() &&  $(this).val() != '') {
    remarkcnt.push($(this).val());
  }

});
//alert(remarkcnt);
if(remarkcnt.length < 1) 
{
    alert('Please filled Once');
    return false;
}
else {
  if (confirm('Are you sure you want to complete first stage?')) {
        /*$.ajax({
            url: 'save_firststage.php',
            type: "POST",
            data: {
                // data stuff here
            },
            success: function () {
                // does some stuff here...
            }
        });*/
        window.location.href = 'http://localhost/Spectra/checkproductscantesting.php';

    }
}
  });

</script>
<script>
$(function() {
//Date range picker
    $('#reservationdate,#reservationdate1').datetimepicker({
       format: "DD/MM/YYYY"
    });
    //Date range picker
   
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
