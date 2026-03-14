<?php
session_start();
$user=$_SESSION['username'];
$pass=$_SESSION['pass'];
include 'DatabaseConfig.php';
$sql = "SELECT * FROM tbl_stage";
	
$query = odbc_exec($conn, $sql);

$sql1 = "SELECT * FROM tbl_product";
	
$query1 = odbc_exec($conn, $sql1);



$sqlstation = "SELECT * FROM tbl_station";
	
$querystation = odbc_exec($conn, $sqlstation);

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
            <h5 >Welcome To Login</h5>
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
					<h3><i class="fa fa-address-card"></i> Start Page</h3>
					</div>
				</div>
			   </div>
			  <div class="card">
			   <div class="card-body formarea smpadding">
			<h4> <span class="badge badge-danger"  id="span_barcode" style="display:none;"></span></h4>
		 <p style="color:red">*Note : 00038317 3AH11052AF501KW2 3006869535/000110 800005226771 </p>
        <br></br>
        <div class="row">
        <div class="col-md-2">
					<label>Scan QR Code:</label>
				</div>
				<div class="col-md-8">
        <input type="text" class="form-control" name="scanqrcode" id="scanqrcode" autofocus>
     
      
      </div>
      <div class="col-md-2">  <input type="button" class="btn btn-success" value="GO" id="scan_id"> </div>
        </div>
         <div class="row">
			    
				<div class="col-md-2">
					<label>Station Name:</label>
				</div>
				<div class="col-md-4">
        <input type="hidden" class="form-control" id="station_id" name="station_id" readonly>
        <input type="text" class="form-control" id="station_name" name="station_name" readonly>

       <!-- <select class="form-control" id="station_id" name="station_id" aria-label="Default select example">
  <option value="">Select Station Name</option>
  <?php while(odbc_fetch_row($querystation)) { 
     echo "<option value='".odbc_result($querystation,'station_id')."'>".odbc_result($querystation,'station_name')."</option>";
 } ?>
</select>
<div id="error_product" style="display:none;color:red">* Please Select Product</div>
--->
				</div>
        <div class="col-md-2">
					<label>Product Name:</label>
				</div>
				<div class="col-md-4">

			<input type="text" class="form-control" id="product_name" name="product_name" readonly>
      <input type="hidden" class="form-control" id="product_id" name="product_id" readonly>

    </div>
			   </div>
         <div class="row">
         <div class="col-md-2">
					<label>MLFB No:</label>
				</div>
				<div class="col-md-4">
			<textarea class="form-control" id="mlfb_num" name="mlfb_num" readonly></textarea>
			   </div>
         <div class="col-md-2">
					<label>Machine Name:</label>
				</div>
				<div class="col-md-4">
			<input type="text" class="form-control" id="machine_name" name="machine_name" readonly>
      <div id="error_panel" style="display:none;color:red">* Enter Panel No.</div>
			   </div>
			   </div>

         <div class="row">
         <div class="col-md-2">
					<label>Subassembly Yes/No:</label>
				</div>
				<div class="col-md-4">
			<input type="text" class="form-control" id="subassem_req" name="subassem_req" readonly>
			   </div>
         </div>
     


         <div>
         <input type="button" value="Start" id="start_step" class="btn btn-primary">
			   </div>
         </br>
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
    
  <div class="row">
	  <div class="col-md-6"><b>Disclaimer :</b> For any queries, please contact email abc@example.com .
	  </div>
	  
<div class="col-md-6 text-right">This is the official website of Seimens.</div>
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

'language': {
        "lengthMenu": "_MENU_ per page",
        "zeroRecords": "No records found",
        "info": "Showing <b>_START_ to _END_</b> (of _TOTAL_)",
        "infoFiltered": "",
        "infoEmpty": "No records found",
        "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>'
    },
	  'destroy': true,
	    "sAjaxSource": "load_barcodedetails.php",
         "aoColumns": [
                    {mData: 'sr_no'},
					          {mData: 'product_name'},
					           {mData: 'barcode'},
                     {mData: 'view'}
                ]
			
    });
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
$('#scan_id').click(function(){
  //alert("Hello");
  var scanqrcode = $('#scanqrcode').val();
   if(scanqrcode.length >= 56)  
	{
	 $.ajax({
                url:'get-stationproductdet.php',
                type:'post',
                dataType: 'json',
                data:{
                  scanqrcode:scanqrcode
                },
                success:function(data){
                 //alert(data.success);
              if(data.notexist == "NOTEXIST"){
					alert("Invalid Barcode");
					
					$("#product_name").val('');
$("#product_id").val('');
//alert(x.floors);
$("#mlfb_num").val('');
$("#machine_name").val('');
$("#station_name").val('');
$("#station_id").val('');
$('#scanqrcode').val('');
					return false;
				}
				
			   
			   else {
				 var x = data.success;
        // $('#scanqrcode').val();
                  $("#product_name").val(x.product_name);
$("#product_id").val(x.product_id);
//alert(x.floors);
$("#mlfb_num").val(x.mlfb_num);
$("#machine_name").val(x.machine_name);
$("#station_name").val(x.station_name);
$("#station_id").val(x.station_id);
if(x.subassembly_req == 'Y'){
   $('#subassem_req').val('Yes');
}
else
{
  $('#subassem_req').val('No');
}
//$('#scanqrcode').disabled(true);
//$('#scanqrcode').attr(‘disabled’,’disabled’);
                }
				
				
				}
            });
  }

});
$('#start_step').on('click', function(){
  var station_id= $('#station_id').val();
  var station_name= $('#station_name').val();
  var product_id= $('#product_id').val();
  var product_name= $('#product_name').val();
  var scanqrcode = $('#scanqrcode').val();

  var mlfb_num =  $('#mlfb_num').val();
  var machine_name =  $('#machine_name').val();
  var subassem_req = $('#subassem_req').val();
  if(station_id != '' && product_id != ''){
  if (confirm('Are you sure you want to start for testing?')) {

    $.ajax({
type: "POST",
url: 'checktestingstage.php',
dataType: 'json',
data: {
       station_id:station_id,
       product_id:product_id,
       subassem_req:subassem_req,
       station_name:station_name,
       product_name:product_name,
       mlfb_num:mlfb_num,
       machine_name:machine_name,
       scanqrcode : scanqrcode
       },
cache: false,


success: function(data){
  var x = data.success;
  if(subassem_req == 'Yes' && x.res == 'NOAVAILABLE'){
    alert('This Product is not Available for Daily Workshit');
     return false;

  }

  else if(x.cnt == 0 && subassem_req == 'Yes' && x.res == 'NOFOUND'){
     alert('Subassembly is not Completed for other station please do first');
     return false;

  }
  else if(x.cnt == 0 && subassem_req == 'Yes' && x.res == 'SUBPRECHECK'){
    window.location.href = "checksubassembly.php";

  }
  else if(x.cnt_min_first < x.cnt_total_first && subassem_req == 'Yes' && x.res == 'SUBPRECHECK'){
    window.location.href = "checksubassemblyview.php";
  }

  
  else if(x.cnt == 1 && subassem_req == 'Yes' && x.res == 'SUBASSEMBLY'){

    window.location.href = "checksubassembly2stage.php";
  }
 /* if(x.cnt == 0 && subassem_req == 'Yes'){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   
    window.location.href = "checksubassembly.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='3' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkassembly.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='4' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkassemblystage2.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='5' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkprechecktesting.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='6' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checktestingstge2.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt == 0 && subassem_req == 'No'){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkassembly.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.stage_id=='1')
 {
  window.location.href = "checksubassembly2stage.php";
 }
 else if(x.stage_id=='2')
 {
  window.location.href = "checklocation.php";
 }

}
*/
}
    });
     
  
  }

  }
  else{
   alert('Select Station first');
   return false;
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
