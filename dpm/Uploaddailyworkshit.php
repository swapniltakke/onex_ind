<?php
include 'DatabaseConfig.php';
session_start();
$user=$_SESSION['username'];
$pass = $_SESSION['pass'];
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
 <!-- Css for datatable -->
 <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<style>

.test {
      width: 170px;
      height: 120px;
      background-color: grey;
    }
    .success {
  background-color: #ddffdd;
  border-left: 6px solid #04AA6D;
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
        <span class="nav-link"><?php echo strtoupper($user); ?> </span>
      </li>
    </ul>

    
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
     
      <!-- Notifications Dropdown Menu -->
      
       <!-- <li class="nav-item d-none d-sm-inline-block">
       <a href="Update_profile.php"> <span class="nav-link"><i class="nav-icon fas fa-user"></i> <?php //echo $pass; ?></span></a>
      </li> -->
     <!-- <li class="nav-item">
        <img src="img/emb.gif" alt="" height="55" />
      </li>-->
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <?php
  include('menu_supervisor.php');
  ?>

 
    <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
	  <div class="pagehead">
        <div class="row">
          <div class="col-sm-4">
            <h5>Upload Daily Production Plan</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <!--<a href="#"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
  <a href="#"  class="tablinks" ><i class="fa fa-money"></i> </a>
  	<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
    <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
	  <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a>-->


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
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
               <div class="new_buttons float-sm-right">
			   
	<!--
               <a href="add_stagelist.php"><span class="icon-input-btn">
		<i class="fa fa-plus text-success"></i> 
		<input type="submit" class="btn btn-default" value="New">
	</span></a>
	<span class="icon-input-btn">
		<i class="fa fa-edit text-primary"></i> 
		<input type="submit" class="btn btn-default" value="Edit">
	</span>
	<span class="icon-input-btn">
		<i class="fa fa-trash text-danger"></i> 
		<input type="submit" class="btn btn-default" value="Delete" disabled>
	</span>
	<span class="icon-input-btn">
		<i class="fa fa-check text-success"></i> 
		<input type="submit" class="btn btn-default" value="Save"disabled>
	</span>
    <span class="icon-input-btn">
		<i class="fa fa-retweet text-warning"></i> 
		<input type="Reset" class="btn btn-default" value="Revert">
	</span>
	<span class="icon-input-btn">
		<i class="fa fa-remove text-danger"></i> 
		<input type="submit" class="btn btn-default" value="Close">
	</span>-->

			   
			   
				
</div>
              
			
<br>	</br>		
			
		
<div class="success">
  <strong>* Note :</strong> <ol><li>Supervisor Upload Daily Production Plan to this Form</li><li>File Format only accepted .CSV Only.</li></ol> 
</div>
			
			
			
	    <div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i>Upload Daily Production Plan</h3>
					</div>
				</div>
			   </div><br>
			<div class="card">
			   <div class="card-body formarea smpadding">
      <?php   if(!empty($_GET['status'])){
    switch($_GET['status']){
        case 'succ':
            $statusType = 'alert-success';
            $statusMsg = 'Members data has been imported successfully.';
            break;
        case 'err':
            $statusType = 'alert-danger';
            $statusMsg = 'Some problem occurred, please try again.';
            break;
        case 'invalid_file':
            $statusType = 'alert-danger';
            $statusMsg = 'Please upload a valid CSV file.';
            break;
        default:
            $statusType = '';
            $statusMsg = '';
    }
}
?>

			   <?php if(!empty($statusMsg)){ ?>
<div class="col-xs-12">
    <div class="alert <?php echo $statusType; ?>"><?php echo $statusMsg; ?></div>
</div>
<?php } ?>
<form action="importData.php" method="post" enctype="multipart/form-data">
			   <div class="row">
         
               <div class="col-md-2" style="text-align:center">
					     <label>Upload File: <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4"><input type="file" name="file" required>              
            <input type="submit" class="btn btn-primary" name="importSubmit" value="IMPORT"></div>
  
			   </div>
         </form>

               <br>
              <div class="display_table">
					<table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th width="20px">Sr. No.</th>
                    <th width="30px">Date</th>
                    <th width="20px">Productin Order</th>
                    <th width="20px">SAPNo</th>
                    <th width="20px">Client</th>
                    <th width="20px">Product</th>
					<th width="20px">Rating</th>
                    <th width="20px">Qty</th>
                    <th width="20px">WB</th>
                    <!--<th width="20px">OIS</th>
                    <th width="20px">ODC MSN</th>
                    <th width="20px">FAT</th>
                    <th width="20px">1st Drive</th>
					<th width="20px">1st Pole</th>
					<th width="20px">2nd Drive</th>
					<th width="20px">2nd Pole</th>
					<th width="20px">Issued</th>
					<th width="20px">Bal</th>
					<th width="20px">Start Date</th>
					<th width="20px">End Date</th>
					<th width="20px">Picking status</th>
					<th width="20px">OCC</th>
					<th width="20px">Delivery Remark</th>-->
          <th width="20px">Progress Status</th>
                    <th width="20px">Action</th>

                  </tr>
                  </thead>
                  <tbody>
        <?php
        // Get member rows
       // $result = "SELECT DISTINCT Product FROM tbl_DailyUpload";
        $result = "SELECT * FROM tbl_DailyUpload ORDER BY uploadid ASC";
        $check_sql = odbc_exec($conn,$result);
       // if($result->num_rows > 0){
         $sr_no = 1;
            while(odbc_fetch_row($check_sql)){
        ?>
            <tr>
                <td><?php echo $sr_no; $sr_no++;?></td>
                <td><?php echo odbc_result($check_sql,'uploaddate'); ?></td>
                <td><?php echo odbc_result($check_sql,'ProductinOrder'); ?></td>
                <td><?php echo odbc_result($check_sql,'SAPNo'); ?></td>
                <td><?php echo odbc_result($check_sql,'Client'); ?></td>
                <td><?php echo odbc_result($check_sql,'Product'); ?></td>
                <td><?php echo odbc_result($check_sql,'rating');?></td>
                <td><?php echo odbc_result($check_sql,'Qty'); ?></td>
                <td><?php echo odbc_result($check_sql,'WB'); ?></td>
			<!--	<td><?php // echo odbc_result($check_sql,'OIS'); ?></td>
				<td><?php //echo odbc_result($check_sql,'ODS_MSN'); ?></td>
				<td><?php //echo odbc_result($check_sql,'FAT'); ?></td>
				<td><?php //echo odbc_result($check_sql,'FirstShiftDrive'); ?></td>
				<td><?php //echo odbc_result($check_sql,'FirstShiftPole'); ?></td>
				<td><?php //echo odbc_result($check_sql,'SecondshiftDrive'); ?></td>
				<td><?php //echo odbc_result($check_sql,'SecondshiftPole'); ?></td>
				<td><?php //echo odbc_result($check_sql,'Issued'); ?></td>
				<td><?php //echo odbc_result($check_sql,'Bal'); ?></td>
				<td><?php //echo odbc_result($check_sql,'StartDate'); ?></td>
				<td><?php //echo odbc_result($check_sql,'EndDate'); ?></td>
				<td><?php //echo odbc_result($check_sql,'PickingStatus'); ?></td>
				<td><?php //echo odbc_result($check_sql,'OCC'); ?></td>
				<td><?php //echo odbc_result($check_sql,'DeliveryRemarks'); ?></td>-->
				<td><?php echo odbc_result($check_sql,'Progress_Status'); ?></td>
                <td> <a class="btn btn-default btn-circle" title="Modify" href="update_dailyworkshit.php?uploadid=<?php echo trim(odbc_result($check_sql, 'uploadid')); ?>"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick='return confirm("Are you sure you want to delete this Checklist?");' title="" href="delete_dailywrokshit.php?uploadid=<?php echo trim(odbc_result($check_sql, 'uploadid'));?>" ><i class="fa fa-trash"></i></a></td>
            </tr>
        <?php }  ?>
           
        </tbody>
                  
                </table>
					</div>
			   </div>
             
            </div>
			
			
			
			
			</div>

          </div>
        </div>
      </div>
    </section>
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

function formToggle(ID){
    var element = document.getElementById(ID);
    if(element.style.display === "none"){
        element.style.display = "block";
    }else{
        element.style.display = "none";
    }
}
</script>
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});
 $('#example1').DataTable({
    'paging'      : true,
	 'lengthChange': true,
      'searching'   : true,
      'ordering'    : false,
      'info'        : true,
	  'stateSave' : true,
      'autoWidth'   : false,
	  'serverSide' : false,
	  'language': {
        "lengthMenu": "_MENU_ per page",
        "zeroRecords": "No records found",
        "info": "Showing <b>_START_ to _END_</b> (of _TOTAL_)",
        "infoFiltered": "",
        "infoEmpty": "No records found",
        "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>'
    }
 });
</script>
</body>
</html>