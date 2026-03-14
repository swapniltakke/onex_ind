<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
$sql3 = "SELECT * FROM tbl_DailyUpload";
$res2 = DbManager::fetchPDOQueryData('spectra_db', $sql3)["data"];
$sql = "SELECT * FROM tbl_workbench";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
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
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <link href="media/jquery.datetimepicker.css" rel="stylesheet" />
<style>

.test {
      width: 170px;
      height: 100px;
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
    <!--<section class="content-header">
      <div class="container-fluid">
	  <div class="pagehead">
        <div class="row">
          <div class="col-sm-4">
            <h5>Report Assembly</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <a href="Supervisordash.php"  class="tablinks" ><i class="fas fa-home"></i> </a>
 
</div>
            
          </div>
        </div>
		</div>
      
    </section> -->

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
              
			
<!--<br>	</br>		-->
			
		
<!--<div class="success">
  <strong>* Note :</strong> <ol><li>Update Workbench Transaction</li></ol> 
</div>-->
			
			
			
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i>Report - Assembly</h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
     
            <!-- Popup Model -->
            <div class="modal fade" id="myModal1" role="dialog">
              <div class="modal-dialog">

    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
        <h4 align="left" class="modal-title" id="work_title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          
        </div>
        <div class="modal-body">
          <input type="hidden" class="form-control" id="trdet_id">
          <label>End Date: </label><br></br>
          <input type="text" class="form-control" name="end_date" id="end_date">


        </div>
        <div class="modal-footer">
        <input type="submit" class="btn btn-warning" value="Update" id="update_workbench">  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>

            <!-- End Popup -->
		
			   <div class="row">
         
               <div class="col-md-2">
					     <label>Production Order: <font color="red"> </font></label>
				    </div>
				    <div class="col-md-4">   
                <select name="prod_order_id" class="select2 form-control" id="prod_order_id">
                    <option value="">Order No</option>
                    <?php foreach ($res2 as $data) {
                            echo '<option value="'.$data['ProductinOrder'].'">'.$data['ProductinOrder'].'</option>';
                    } ?>
                </select>	          
            </div>
					
                    
                    <div class="col-md-2">
					     <label>Select Date: <font color="red"> </font></label>
				    </div>
                    <div class="col-md-4">   
					<input type="text" class="form-control" name="upload_date" value ="<?php echo date('Y-m-d'); ?>" id="upload_date" >				
                    <!--<select name="product_id" class="select2 form-control" id="product_id">
                    <option value="">Select Product</option>
                     </select>-->
                                </div>
  
			   </div>
               <!--<div class="row">
                     <div class="col-md-2">
                     <label>Select Workbench: <font color="red"> </font></label>
                    </div>
                    <div class="col-md-4">   
                    <select name="work_id" class="select2 form-control" id="work_id">
                            <option value="">Select Workbench</option>
							
                        </select>	          
                    </div>

                </div> 

               <br></br>-->
               <div class="card" >
			   <div class="card-body formarea smpadding">
               <button type="submit" id="update_det" class="btn btn-sm btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp; &nbsp;&nbsp;</span>

               </div>
            </div>  
            <div class="card" id="card_display" style="display:none;">
			   <div class="card-body formarea smpadding">      

                      <div class="row">
                  
                  <div class="col-md-2">
                  <label>Documented Procedure No.: <font color="red"> </font></label>
                </div>
                <div class="col-md-4"> 
                   <div id="proc_no"></div>

                </div>
                <div class="col-md-2">
                  <label> Order No./Item No </label>
                </div>
                <div class="col-md-4"> 
                   <div id="order_item_no"></div>

                </div>
              </div> 
              <br>
              <div class="row">
                <div class="col-md-2">
                  <label>Annex Assembly checklist for IVCB</label>
                </div>
                <div class="col-md-4"> 
                   <div id="annx_assm_ivcb"></div>
                </div>
                <div class="col-md-2">
                   <label>Client No  </label>           
                </div>
                <div class="col-md-4"> 
                   <div id="client_no"></div>
                </div>
              </div><br>
              <div class="row">
                <div class="col-md-2">
                  <label>Siemens AG Business Unit SI EA</label>
                </div>
                <div class="col-md-4"> 
                   <div id="seimens_ag"></div>
                </div>
                <div class="col-md-2">
                   <label>VCB Sr No </label>           
                </div>
                <div class="col-md-4"> 
                   <div id="vsb_sr_no"></div>
                </div>
              </div>


              </div>
              </div> 
              <div class="display_table">
					<table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <!--<th width="20px">Sr. No.</th>
                    <th width="30px">Station</th>
                    <th width="20px">Activity</th>
                    <th width="20px">Check</th>
                    <th width="20px">Operator</th> -->
                    <!--<th width="20px">Start Date</th>-->
                    <!--<th width="20px">End Date</th>-->
					          <th width="20px">Sr. No.</th>
                    <th width="30px">Station ID</th>
                    <th width="20px">Activity</th>
                    <th width="20px">Check</th>
                    <th width="20px">Operator</th>
                    
					

                  </tr>
                  </thead>
                 
                   
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
  <script src="media/jquery.datetimepicker.full.min.js"></script>

<script>
$('#end_date').datetimepicker();
$('#upload_date').datetimepicker();

    $(function(){

 $("#upload_date").change(function () {
        var station_id = $(this).val();
        var dataString = 'upload_date=' + station_id ;
        //alert(dataString);

        $.ajax({
            type: "POST",
            url: "get-orderlist.php",
            data: dataString,
            cache: false,
            success: function (html) {
                $("#prod_order_id").html(html);
            }
        });
    });

});

//

$(function(){

$("#prod_order_id").change(function () {
       var prod_order_id = $(this).val();
       var dataString = 'ProductinOrder=' + prod_order_id ;
       //alert(dataString);

       $.ajax({
           type: "POST",
           url: "get-dailyuploaddetails.php",
           data: dataString,
           cache: false,
           success: function (data) {
              // $("#product_id").html(html);
               if(data.notexist == "NOTEXIST"){
					//alert("Invalid Barcode");
                      $("#proc_no").html('');
					            $("#order_item_no").html('');
          

					return false;
				}
				
			   
			   else {
				 var x = data.success;
        // $('#scanqrcode').val();
                 // $("#product_name").val(x.product_name);
                 $("#card_display").show();

                  $("#proc_no").html('');
					            $("#order_item_no").html(x.SAPNo);
                      $("#vsb_sr_no").html('00038317');
                      $("#annx_assm_ivcb").html(x.Product);
                      $("#client_no").html(x.Client);
                      
                      


                }

           }
       });
   });

});

//

$('#update_det').on('click', function(){
  var prod_order_id = $('#prod_order_id').val();
  var upload_date = $('#upload_date').val();
  //alert(''+prod_order_id);
  $('#example1').DataTable({
      "paging": false,
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
	
     "ajax": {
        'type': 'POST',
        'url': 'load_reportupdate.php',
        'data': function (data) {
        data.prod_order_id = prod_order_id;
        data.upload_date = upload_date;
	
		
    }
		},

        "aoColumns": [
          {mData: 'sr_no',sWidth: '1%'},
          {mData: 'station_name',sWidth: '10%'},
          {mData: 'activity',sWidth: '10%'},
          {mData: 'check',sWidth: '20%'},
        
          {mData: 'remark',sWidth: '10%'}
          ]
			
        });
  
});

$('#workdench_det').on('click', function(){

var station_id = $('#station_id').val();
var product_id = $('#product_id').val();
var work_id = $('#work_id').val();
alert(station_id);
alert(product_id);
//alert(work_id);
//if(station_id == '' && product_id == '' && work_id == ''){
//    alert('Please Select All Details');
//    return false;
//}
//else{
    $('#example1').DataTable({
      "paging": false,
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
	
     "ajax": {
        'type': 'POST',
        'url': 'load_workbenchtrans1.php',
        'data': function (data) {
        data.product_id = product_id;
		data.station_id= station_id;
        data.work_id = work_id;
		
    }
		},

        "aoColumns": [
          {mData: 'sr_no',sWidth: '1%'},
          {mData: 'station_name',sWidth: '10%'},
          {mData: 'product_name',sWidth: '10%'},
          {mData: 'stage_name',sWidth: '20%'},
          {mData: 'w_type',sWidth: '10%'},
          {mData: 'start_time',sWidth: '10%'},
        //  {mData: 'end_time',sWidth: '10%'},
		  {mData: 'Barcode',sWidth: '10%'},
          {mData: 'remark',sWidth: '10%'},
          { mData : 'trdet_id',
              mData: 'action',
          sWidth: '10%',
          render: function (mdata, type, row, meta){
                         return '<a class="update_work_id btn btn-default btn-circle"  data-id="'+row['trdet_id']+'" data-toggle="modal"  data-target="#myModal1" title="Modify"><i class="fa fa-edit"></i></a>';
                      }
          
          
          }
          ]
			
        });

//}



});



       $('.select2').select2();
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

//$('#example1').on('click','.update_work_id',function()
//{
  //alert('Hello');
  //var tr_id = $(this).attr('data-id');
  //$('#trdet_id').val(tr_id);
  //alert(tr_id);
//});

$('#update_workbench').on('click', function(){
  var product_id = $('#product_id').val();
var station_id = $('#station_id').val();
  var work_id = $('#work_id').val();
  //alert(''+work_id);

  var trdet_id = $('#trdet_id').val();
  
  var end_date = $('#end_date').val();
  
  if(end_date == ""){
    alert("Please Select End Date");
    return false;
  }
  else {
//alert('Hello');
$.ajax({
  url: 'update_workbench_enddate.php',
           type: "POST",
           data: {
              product_id : product_id,
              station_id : station_id,
              work_id : work_id,
              trdet_id: trdet_id,
              end_date : end_date

              
           },
           success: function (data) {

            var x = data.success;
            if(x.update == "UPDATE")
                   {
                    alert('Record is Upadated');
                    
                    $('#trdet_id').val('');
                   $('#end_date').val('');
                  
                  $('#myModal1').modal('hide');
                  $('#workdench_det').trigger('click');

                   }

           }




});
  }



}); 
/* $('#example1').DataTable({
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
 });*/
</script>
</body>
</html>