<?php
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
include('header.php');
include('menu_spectra.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
	  <div class="pagehead">
        <div class="row">
          <div class="col-sm-4">
            <h5>Basic Error Description</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <!--<a href="#"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
   <a href="#"  class="tablinks" ><i class="fa fa-money"></i> </a>
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
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
               <div class="new_buttons float-sm-right">
			   
	
               <a href="add_error.php"><span class="icon-input-btn">
		<i class="fa fa-plus text-success"></i> 
		<input type="submit" class="btn btn-default" value="New">
	</span></a>
	<!--<span class="icon-input-btn">
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
	</span>-->
	<span class="icon-input-btn">
		<i class="fa fa-remove text-danger"></i> 
		<input type="submit" class="btn btn-default" value="Close">
	</span>

			   
			   
				
</div>
              
			
<br>	</br>		
			
		
			
			
			
			
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i>Error Details</h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   
			   <div class="row">
				<div class="col-md-12">
					<div class="display_table">
					<table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Error Name</th>
                    <th>Description</th>
                    <th>Error Type</th>
                    <th>Action</th>
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
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

 <?php  include('footer.php'); ?>
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
var table=  $('#example1').DataTable({
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
    },
	  'destroy': true,
	    "sAjaxSource": "load_errorlist.php",
         "aoColumns": [
                    {mData: 'error_id'},
					{mData: 'error_name'},
					{mData: 'description'},
                     {mData: 'error_type'},
					{mData: 'action'}
                ]
			
			 
    
    })
	;
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
