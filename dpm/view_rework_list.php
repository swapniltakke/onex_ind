<?php
session_start();
$user=$_SESSION['username'];
$pass=$_SESSION['pass'];
include('shared/CommonManager.php');
$product_name = $_SESSION['product_name'];
$station_name = $_SESSION['station_name'];
$product_id = $_SESSION['product_id'];
$station_id = $_SESSION['station_id'];
$stage_id = $_REQUEST['stage_id'];
$work_id = $_REQUEST['work_id'];
$sql = "SELECT mw.w_type,p.product_name,s.station_name,td.start_time,td.end_time,td.remark
FROM tbl_transactiondetails td
inner join tbl_manageworkbench mw on mw.w_id=td.w_type
inner join tbl_product p on p.product_id=td.product_id
inner join tbl_station s on s.station_id=td.station_id
and td.station_id=:station_id and td.product_id=:product_id and td.tr_id=:stage_id";
//echo $sql;	 exit;
$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":stage_id" => "$stage_id", ":product_id" => "$product_id", ":station_id" => "%,$station_id,%"])["data"];




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
		<li><a href="Admindash.php"><div class="link"><i class="fa fa-dashboard"></i><span>Dashboard</span></div></a></li>
  <li><a href="barcodegenerate.php"><div class="link"><i class="fa fa-hourglass-start"></i><span>Start </span></div></a></li>

<li><div class="link"><a href="Viewproducttestings.php"><i class="fa fa-dashboard"></i><span>View Test</span></a></div></li>
<!--<li><div class="link"><a href="checkproducttesting.php"><i class="fa fa-dashboard"></i><span>Testing Test</span></a></div></li>

<li><div class="link"><a href="checksubassembly.php"><i class="fa fa-dashboard"></i><span>Subassembly Test</span></a></div></li>

  
  
-->
   <li>
   <A href="logout.php"> <div class="link"> <i class="fa fa-sign-out nav-icon"></i><span>Logout</span></a></div>
   
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
            <h5 ></h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <a href="afterLogin.html"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
  <a href="#"  class="tablinks paybill" ><i class="fa fa-money"></i> </a>
  	<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
    <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
	  <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a>
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
					<h3><i class="fa fa-address-card"></i> View SOP Uploded PDFS</h3>
					</div>
				</div>
			   </div>
			  <div class="card">
			   <div class="card-body formarea smpadding">
			<h4> <span class="badge badge-danger"  id="span_barcode" style="display:none;"></span></h4>
        <br></br>


        <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Product Name</th>
                    <th>Station Name</th>
                    <th>Rework Type</th>
                    <th>Remark</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                    
                  </tr>
                  </thead>
                  <tbody>
                  <?php $sr = 1;foreach ($query as $query1) { ?>
                    <tr>
                        <td><?php echo $sr; $sr++; ?></td>
                        <td><?php echo ($query1['product_name']); ?></td>
                        <td><?php echo ($query1['station_name']);; ?></td>
                        <td><?php echo ($query1['w_type']); ?></td>
                        <td><?php echo ($query1['remark']); ?> </td>
                        <td> <?php echo ($query1['start_time']); ?></td>
                        <td> <?php if(($query1['end_time']) != '1900-01-01 00:00:00.000') echo ($query1['end_time']); ?></td>
                    
                        <td><?php echo '<a class="btn btn-danger btn-circle remove"  data-placement="top" data-original-title=""  title=""  ><i class="fa fa-trash"></i></a>'; ?></td>
                  </tr>



                    <?php } ?>
                    <tbody>
                  <tfoot>
                  <tr>
                  <th>Sr. No.</th>
                    <th>Product Name</th>
                    <th>Station Name</th>
                    <th>Rework Type</th>
                    <th>Remark</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                   
                  </tr>
                  </tfoot>
                </table>
       
              <!-- /.card-body -->
              <!--<div class="card-footer">
                Footer
              </div>-->
              <!-- /.card-footer-->
            </div>
			
			
			
			
            <!-- /.card -->
          
          <!--  <div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-address-card"></i>  Details</h3>
					</div>
				</div>
			   </div>
			  <div class="card">
			   <div class="card-body formarea smpadding">
               <div class="display_table">
               <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>View</th>
                    
                  </tr>
                  </thead>
                 
                  <tfoot>
                  <tr>
                  <th>Sr. No.</th>
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>View</th>
                   
                  </tr>
                  </tfoot>
                </table>
               </div>

               </div>
               </div>
  
			

 Row div End -->                           
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
    
    <div class="col-md-12 text-center">
      2025 DPM</div>
        
    <div class="col-md-12 text-center">
        Designed and Developed for SI EA O AIS THA
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

			
    });
  });
  $(".remove").click(function(){

var id = $(this).parents("tr").attr("id");


if(confirm('Are you sure to remove this record ?'))

{

    $.ajax({

       url: 'delete_sop_files.php',

       type: 'GET',

       data: {sr_id: id},

       error: function() {

          alert('Something is wrong');

       },

       success: function(data) {

            $("#"+id).remove();

            alert("Record removed successfully");  

       }

    });

}

});
 /* $(document).ready(function(){
// code to get all records from table via select box
$("#station_id").change(function() {
var station_id = $(this).find(":selected").val();
var dataString = 'station_id='+ station_id;
//alert(society_id);
$.ajax({
type: "POST",
url: 'get-stationproductdet.php',
dataType: 'json',
data: dataString,
cache: false,
success: function(data){
var x = data.success;
//alert(x);
//var data=data.success;
$("#product_name").val(x.product_name);
$("#product_id").val(x.product_id);
//alert(x.floors);
$("#mlfb_num").val(x.mlfb_num);
$("#machine_name").val(x.Machine_name);
if(x.subassembly_req == 'Y'){
   $('#subassem_req').val('Yes');
}
else
{
  $('#subassem_req').val('No');
}
} 
});
});
});


$(function () {
var ajaxRequestMade = false;
$('#scanqrcode').keyup(function() {

  var scanqrcode =$('#scanqrcode').val();
var dataString = 'scanqrcode='+ scanqrcode;
if(scanqrcode.length >= 53 && !ajaxRequestMade){
$.ajax({
type: "POST",
url: 'get-stationproductdet.php',
dataType: 'json',
data: dataString,
cache: false,
success: function(data){
var x = data.success;
//alert(x);
//var data=data.success;
$("#product_name").val(x.product_name);
$("#product_id").val(x.product_id);
//alert(x.floors);
$("#mlfb_num").val(x.mlfb_num);
$("#machine_name").val(x.Machine_name);
if(x.subassembly_req == 'Y'){
   $('#subassem_req').val('Yes');
}
else
{
  $('#subassem_req').val('No');
}
} 
});
}
});


});*/


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
