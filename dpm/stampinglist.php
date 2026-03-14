<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
include('header.php');
include('menuwarehouse.php');
?>
<style>
.text-wrap{
    white-space:normal;
}
.width-200{
    width:200px;
}

</style>
  <!-- <div class="modal fade" id="SampleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">
 <h4 class="modal-title" id="myModalLabel"><center>Add Product List</center></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                   

                </div>

                <div class="modal-body">
                  
                <label>Upload Excel File</label>

<input type="file" id="file_product" name="file_product" class="form-control">
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                    <input type="button" id="save_product" value="Save" class="btn btn-primary">

                </div>

            </div>

        </div>

    </div> -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
	  <div class="pagehead">
        <div class="row">
          <div class="col-sm-4">
            <h5>Warehouse Scan List</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
 <!-- <a href="#"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
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
			   
	
               <!-- <a href="add_productlist.php"><span class="icon-input-btn">
		<i class="fa fa-plus text-success"></i> 
		<input type="submit"  class="btn btn-default" value="New">
	</span></a> -->
	
    <!--<span class="icon-input-btn">
		<i class="fa fa-retweet text-warning"></i> 
		<input type="submit"  id="btnShow" class="btn btn-default" value="Import">
	</span>-->
	<!-- <span class="icon-input-btn">
		<i class="fa fa-remove text-danger"></i> 
		<input type="submit" class="btn btn-default" value="Close">
	</span> -->

			   
			   
				
</div>
              
			
<br>	</br>		
			
		
			
			
			
			
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Warehouse Details</h3>
					</div>
				</div>
			   </div>
         
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <div class="row">
        
				<div class="col-md-12">
        <div>
         <div class="blockhead">
         <form id="dateRangeForm">
              <div style="text-align:center;">
              <label for="fromDate" class="col-md-2" >From Date:</label>
              <input class="col-md-2" type="date" id="fromDate" name="fromDate" required>
              
              <label for="toDate" class="col-md-2">To Date:</label>
              <input class="col-md-2" type="date" id="toDate" name="toDate" required>
             
              <button  style="padding: 6px 10px; background-color: #009999; color: white; border: none; border-radius: 4px; cursor: pointer;" type="submit">Export to Excel</button>
              <!-- <button  style="padding: 6px 10px; background-color: #009999; color: white; border: none; border-radius: 4px; cursor: pointer;" type="submit">Export to PDF</button> -->

              </div>
          </form>
          </div>
</div>
            
					<div class="display_table">
          
           <!--<center><button id="exportExcel">Export to Excel</button> 
              <button id="exportPDF">Export to PDF</button></center> -->
					<table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sr.No.</th>
                    <!-- <th>Product ID</th> -->
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>Serial No.</th>
                    <th>MLFB No.</th>
                    <th>Sales Order No.</th>
                    <th>Item No.</th>
                    <th>Production Order No.</th>
                    <th>Remarks</th>
                    <th>Date</th>
                    <!-- <th>Action</th> -->
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

  <?php include('footer.php') ?>
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});

$("#save_product").click(function () {
  var file_product= $('#file_product').val();
 
  $.ajax({
            //url: 'file_upload_product.php',
            type: "POST",
            data: {
              file_product: file_product  
            },
            success: function () {
                // does some stuff here...
            }
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
	    "sAjaxSource": "load_warehousedetails.php",
         "aoColumns": [
                    {mData: 'sr_no'},
                    {mData: 'procuct_name'},
                    {mData: 'barcode'},
                    {mData: 'part1'},
                    {mData: 'part2'},
                    {mData: 'part3'},
                    {mData: 'part4'},
                    {mData: 'part5'},
                    {mData: 'remark'},
                    {mData: 'up_date'}
                    ],
            columnDefs: [
            {
              render: function (data, type, full, meta) {
                    return "<div style='width:422px;'>" + data + "</div>";
                },
                targets: 2
            },
            {
                render: function (data, type, full, meta) {
                    return "<div class='text-wrap width-200'>" + data + "</div>";
                },
                targets: 4
            }
          ]
			
			 
    
    })
	;
  $('#dateRangeForm').submit(function(event) {
    event.preventDefault(); // Prevent the form from submitting the traditional way

    var fromDate = $('#fromDate').val();
    var toDate = $('#toDate').val();

    // Redirect to the export script with date parameters
    window.location.href = 'export_excel_warehouse.php?fromDate=' + fromDate + '&toDate=' + toDate;


});

$('#exportPDF').click(function() {
  window.location.href = 'export_pdf_warehouse.php';

});

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
 $(document).ready(function () {

            $("#btnShow").click(function () {

                $('#SampleModal').modal('show');

            });

        });
</script>

<script>
        document.getElementById('downloadExcel').addEventListener('click', function() {
            const table = document.getElementById('dataTable');
            const rows = Array.from(table.querySelectorAll('tr'));
            const csvContent = rows.map(row => 
                Array.from(row.querySelectorAll('th, td'))
                .map(cell => cell.textContent)
                .join(',')
            ).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'data.xlsx');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
</body>
</html>
